<?php

include_once('util/http.php');
include_once('util/file.php');

class ResyncChangelist {

    // The URL of the changelist
    public $url;

    // The urlset raw XML
    private $xml;

    // How many files created
    private $createdcount = 0;

    // How many files created
    private $updatedcount = 0;

    // How many files created
    private $deletedcount = 0;

    // How many files downloaded
    private $downloadcount = 0;

    // How many files skipped
    private $skipcount = 0;

    // URLs of files processed
    private $urls;

    // How long the downloads took (in seconds)
    private $downloadtimer = 0;

    // How large the downloads were (in kilobytes)
    private $downloadsize = 0;

    // The callback function to run after each download
    private $createcallback;

    // The callback function to run after each download
    private $updatecallback;

    // The callback function to run after each download
    private $deletecallback;

    // Whether or not to display debug information
    private $debug = false;

    // Whether the debugging messages are for display in HTML or not
    private $htmldebug = false;

    // Create the new changelist
    function __construct($url) {
        $this->url = $url;
        $xmllist = http_get($this->url);
        $xml = simplexml_load_string($xmllist);

        // Is this a sitemapindex (changelist-archive) or a urlset?
        if ($xml->getName() == 'sitemapindex') {
            $this->sitemap = true;
            $this->sitemapxml = $xml;
        } else {
            $this->sitemap = false;
            $this->xml = $xml;
        }
    }

    // Register a callback function for each URL downloaded
    public function registerCreateCallback($callback) {
        $this->createcallback = $callback;
    }

    // Register a callback function for each URL downloaded
    public function registerUpdateCallback($callback) {
        $this->updatecallback = $callback;
    }

    // Register a callback function for each URL downloaded
    public function registerDeleteCallback($callback) {
        $this->deletecallback = $callback;
    }

    // Process the change list
    function process($directory, $lastrun = '', $checksum = true, $force = false, $pretend = false) {
        // Start the timer
        $starttime = microtime(true);

        // File counter
        $total = count($this->xml->url);
        $count = 1;
        $this->urls = array();

        // Was a date given?
        if ($lastrun == '') {
            $lastrun = new DateTime("0000-01-01T01:00:00Z", new DateTimeZone("UTC"));
        }

        // Do we need to first process a sitemapindex?
        if ($this->sitemap) {
            $this->processSitemapindex($this->sitemapxml, $directory, $lastrun, $checksum, $force, $pretend);
        } else {
            $this->processUrlset($directory, $lastrun, $checksum, $force, $pretend);
        }

        // End the timer
        $endtime = microtime(true);
        $this->downloadtimer = $endtime - $starttime;
    }

    private function processSitemapindex($sitemapxml, $directory, $lastrun, $checksum = true, $force = false, $pretend = false) {
        // Namespace handling
        $namespaces = $this->sitemapxml->getNameSpaces(true);
        if (!isset($namespaces['sm'])) $sac_ns['sm'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $sitemaps = $this->sitemapxml->children($namespaces['sm'])->sitemap;

        // Sitemap counter
        $total = count($sitemaps);
        $count = 1;

        // Iterate through each sitemap->loc
        foreach($sitemaps as $sitemap) {
            $this->debug('Processing sitemap (' . $count++ . ' of ' . $total . '): ' . $sitemap->loc);

            $xmllist = http_get($sitemap->loc);
            $xml = simplexml_load_string($xmllist);

            // Is this a sitemapindex or a urlset?
            if ($xml->getName() == 'sitemapindex') {
                $this->processSitemapindex($xml, $directory, $lastrun, $checksum, $force, $pretend);
            } else {
                $this->xml = $xml;
                $this->processUrlset($directory, $lastrun, $checksum, $force, $pretend);
            }
        }
    }

    private function processUrlset($directory, $lastrun, $checksum = true, $force = false, $pretend = false) {
        // Namespace handling
        $namespaces = $this->xml->getNameSpaces(true);
        if (!isset($namespaces['sm'])) $sac_ns['sm'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $urls = $this->xml->children($namespaces['sm'])->url;

        // File counter
        $total = count($urls);
        $count = 1;

        // Iterate through each url
        foreach($urls as $url) {
            $namespaces = $url->getNameSpaces(true);
            $rs = $url->children($namespaces['rs']);
            $changetype = (string)$rs->md[0]->attributes()->change;
            $this->debug('Processing change (' . $count++ . ' of ' . $total . '): ' . $url->loc . ' = ' . strtoupper($changetype));

            if (($changetype == 'created') || ($changetype == 'updated')) {
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
                $hash = (string)$rs->md[0]->attributes()->hash;
                $algorithm = substr($hash, 0, strpos($hash, ':'));
                $cksum = substr($hash, strpos($hash, ':') + 1, (strlen($hash) - strlen($algorithm) - 1));

                // Does the file exist already?
                $exists = false;
                if (file_exists($build) && (! $force) && (! $pretend)) {
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
                if (($lastmod > $lastrun) && (! $exists)) {
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
                } else if ($exists) {

                } else {
                    $this->debug('  - Skipping! lastmod: ' . $lastmod->format('Y-m-d H:i:s') .
                        ' is before last run: ' . $lastrun->format('Y-m-d H:i:s'));
                    $this->skipcount++;
                }

                // Checksum the file?
                if (($checksum) && (!$exists) && (! $pretend)) {
                    $md5 = base64_encode(md5_file($build, true));
                    $this->debug(' - Checking checksum: ' . $algorithm . ' with value of ' . $cksum);
                    if ($md5 == $cksum) {
                        $this->debug('  - MD5 matched: Expected:' . $cksum . ' Actual:' . $md5);
                    } else {
                        echo '  - ERROR MD5 mismatch: Expected:' . $cksum . ' Actual:' . $md5 . "\n";
                    }
                }

                if ($changetype == 'created') {
                    $this->createdcount++;

                    // Run the callback method
                    if (!empty($this->createcallback)) {
                        call_user_func($this->createcallback, $build);
                    }
                } else {
                    $this->updatedcount++;

                    // Run the callback method
                    if (!empty($this->updatecallback)) {
                        call_user_func($this->updatecallback, $build);
                    }
                }
            } else if ($changetype == 'deleted') {
                // Create the directory if required
                $parts = explode('/', substr($url->loc, 7));
                $build = $directory;
                for ($p = 0; $p < count($parts) - 1; $p++) {
                    $build = $build . '/' . $parts[$p];
                }
                $build .= '/' . $parts[count($parts) - 1];
                echo $build . "\n";
                if (file_exists($build)) {
                    echo ' - Deleting file ' . $build . "\n";
                } else {
                    echo ' - File does not exist!' . "\n";
                }
                $this->deletedcount++;

                // Run the callback method
                if (!empty($this->deletecallback)) {
                    call_user_func($this->deletecallback, $build);
                }
            }

            $this->urls[(string)$url->loc] = $changetype;
        }
    }

    // Return the number of created files
    function getCreatedCount() {
        return $this->createdcount;
    }

    // Return the number of updated files
    function getUpdatedCount() {
        return $this->updatedcount;
    }

    // Return the number of deleted files
    function getDeletedCount() {
        return $this->deletedcount;
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

    // Display a debug mesage
    private function debug($message) {
        if ($this->debug) echo $message;
        if ($this->htmldebug) {
            echo "<br />\n";
            flush();
            ob_flush();
        } else {
            echo "\n";
        }
    }
}
?>