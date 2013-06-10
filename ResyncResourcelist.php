<?php

include_once('util/http.php');
include_once('util/file.php');
include_once('ResyncURL.php');

class ResyncResourcelist {

    // The URL of the resource list
    public $url;

    // The urlset raw XML
    private $xml;

    // The sitemapindex raw XML
    private $sitemapxml;

    // Is the top level file a sitemapindex or urlset
    private $sitemap = false;

    // URLs of files processed
    private $urls;

    // How many files downloaded
    private $downloadcount = 0;

    // How many files skipped
    private $skipcount = 0;

    // How long the downloads took (in seconds)
    private $downloadtimer = 0;

    // How large the downloads were (in kilobytes)
    private $downloadsize = 0;

    // The callback function to run after each download
    private $callback;

    // Whether or not to display debug information
    private $debug = false;

    // Whether the debugging messages are for display in HTML or not
    private $htmldebug = false;

    // Create the new resourcelist
    function __construct($url) {
        $this->url = $url;
        $xmllist = http_get($this->url);
        $xml = simplexml_load_string($xmllist);

        // Is this a sitemapindex or a urlset?
        if ($xml->getName() == 'sitemapindex') {
            $this->sitemap = true;
            $this->sitemapxml = $xml;
        } else {
            $this->sitemap = false;
            $this->xml = $xml;
        }
    }

    // Register a callback function for each URL downloaded
    public function registerCallback($callback) {
        $this->callback = $callback;
    }

    // Baseline sync (download everything)
    // $directory = Where to store the files
    // $from = Date of last run
    // $clear = Whether to remove all files before running (normally false)
    // $checksum = Whether to check file checksums (normally true)
    // $force = Whether to force the download of files, even if they haven't changed (normally false)
    // $pretend = Pretend to download files - useful for testing large syncs (normally false)
    function baseline($directory, $from = '', $clear = false, $checksum = true, $force = false, $pretend = false) {
        // Start the timer
        $starttime = microtime(true);

        // First clear the directory?
        if ($clear) {
            rmdir_recursive($directory, false);
        }
        $this->urls = array();

        // Was a date given?
        if ($from == '') {
            $from = new DateTime("0000-01-01T01:00:00Z", new DateTimeZone("UTC"));
        }

        // Do we need to first process a sitemapindex?
        if ($this->sitemap) {
            $this->processSitemapindex($this->sitemapxml, $directory, $from, $checksum, $force, $pretend);
        } else {
            $this->processUrlset($directory, $from, $checksum, $force, $pretend);
        }

        // End the timer
        $endtime = microtime(true);
        $this->downloadtimer = $endtime - $starttime;
    }

    private function processSitemapindex($sitemapxml, $directory, $from, $checksum = true, $force = false, $pretend = false) {
        // Sitemap counter
        $total = count($sitemapxml->sitemap);
        $count = 1;

        // Iterate through each sitemap->loc
        foreach($sitemapxml->sitemap as $url) {
            $this->debug('Processing sitemap (' . $count++ . ' of ' . $total . '): ' . $url->loc);

            $xmllist = http_get($url->loc);
            $xml = simplexml_load_string($xmllist);

            // Is this a sitemapindex or a urlset?
            if ($xml->getName() == 'sitemapindex') {
                $this->processSitemapindex($xml, $directory, $from, $checksum, $force, $pretend);
            } else {
                $this->xml = $xml;
                $this->processUrlset($directory, $from, $checksum, $force, $pretend);
            }
        }
    }

    private function processUrlset($directory, $from, $checksum = true, $force = false, $pretend = false) {
        // Namespace handling
        $namespaces = $this->xml->getNameSpaces(true);
        if (!isset($namespaces['sm'])) $sac_ns['sm'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $urls = $this->xml->children($namespaces['sm'])->url;

        // File counter
        $total = count($urls);
        $count = 1;

        // Iterate through each url
        foreach($urls as $url) {
            $this->debug('Processing file (' . $count++ . ' of ' . $total . '): ' . $url->loc);

            // Create the directory if required
            $parts = explode('/', substr($url->loc, 7));
            $build = $directory;
            for ($p = 0; $p < count($parts) - 1; $p++) {
                $build = $build . '/' . $parts[$p];
                if (!file_exists($build)) {
                    mkdir($build);
                    $this->debug(' - Adding directory: ' . $build);
                }
            }
            $build .= '/' . $parts[count($parts) - 1];

            // Unencode the checksum
            $rs = $url->children($namespaces['rs']);
            $hash = (string)$rs->md[0]->attributes()->hash;
            $hashexists = true;
            if (!empty($hash)) {
                $algorithm = substr($hash, 0, strpos($hash, ':'));
                $cksum = substr($hash, strpos($hash, ':') + 1, (strlen($hash) - strlen($algorithm) - 1));
            } else {
                $this->debug(' - Missing file hash!');
                $hashexists = false;
            }

            // Does the file exist already?
            $exists = false;
            if (file_exists($build) && (! $force) && (! $pretend) && ($hashexists)) {
                // Does the checksum match?  If so, we can skip the download later
                $md5 = base64_encode(md5_file($build, true));
                $this->debug(' - File already exists, check the checksum:');
                if ($md5 == $cksum) {
                    $exists = true;
                    $this->debug('  - Checksum matches, skipping...');
                    $this->skipcount++;
                } else {
                    $this->debug('  - Checksum does not match, not skipping');
                }
            }

            // Check if file has been updated since last run?
            $lastmod = new DateTime($url->lastmod, new DateTimeZone("UTC"));
            if (($lastmod > $from) && (! $exists)) {
                $this->debug(' - Downloading file: ' . $build);
                if (! $pretend) {
                    $start = microtime(true);
                    http_get_save($url->loc, $build);
                    $filesize = filesize($build);
                    $end = microtime(true);
                    $timer = $end - $start;
                    $speed = $filesize / $timer;
                    $this->debug('  - Filesize: ' . $filesize . ' Time: ' . $timer . ' Speed: ' . $speed);
                    $this->downloadsize += $filesize;
                } else {
                    $this->debug('  - PRETEND mode: not really downloading file');
                }
                $this->downloadcount++;
                $this->urls[(string)$url->loc] = "created";
            } else if ($exists) {

            } else {
                $this->debug('  - Skipping! lastmod: ' . $lastmod->format('Y-m-d H:i:s') .
                             ' is before last run: ' . $from->format('Y-m-d H:i:s'));
                $this->skipcount++;
            }

            // Checksum the file?
            if (($checksum) && (!$exists) && (! $pretend) && $hashexists) {
                $md5 = base64_encode(md5_file($build, true));
                $this->debug(' - Checking checksum: ' . $algorithm . ' with value of ' . $cksum);
                if ($md5 == $cksum) {
                    $this->debug('  - MD5 matched: Expected:' . $cksum . ' Actual:' . $md5);
                } else {
                    echo '  - ERROR MD5 mismatch: Expected:' . $cksum . ' Actual:' . $md5 . "\n";
                }
            }

            // Run the callback method
            if (!empty($this->callback)) {
                call_user_func($this->callback, $build, new ResyncURL($url->loc, $url, $build));
            }
        }
    }

    // Return the number of downloaded files
    function getDownloadedFileCount() {
        return $this->downloadcount;
    }

    // Return the number of skipped files
    function getSkippedFileCount() {
        return $this->skipcount;
    }

    // Return the total download size
    function getDownloadSize() {
        return $this->downloadsize;
    }

    // Return the total download duration
    function getDownloadDuration() {
        return $this->downloadtimer;
    }

    // Return the URLs processed
    function getURLs() {
        return $this->urls;
    }

    // Whether to display debug messages or not
    function enableDebug($debug = true, $html = false) {
        $this->debug = $debug;
        $this->htmldebug = $html;
    }

    // Display a debug message
    private function debug($message) {
        if ($this->debug) echo $message . "\n";
        if ($this->htmldebug) {
            echo "<br />\n";
            flush();
            ob_flush();
        }
    }
}
?>