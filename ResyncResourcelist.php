<?php

include_once('util/http.php');
include_once('util/file.php');

class ResyncResourcelist {
    // The URL of the resource list
    public $url;

    // The resourcelist raw XML
    private $xml;

    // Whether or not to display debug information
    private $debug = true;

    // Create the new resourcelist
    function __construct($url) {
        $this->url = $url;
        $xmllist = http_get($this->url);
        $this->xml = simplexml_load_string($xmllist);
        if ($this->debug) print_r($this->xml);
    }

    // Baseline sync (download everything)
    function baseline($directory, $lastrun, $clear = false, $checksum = true, $force = false) {
        // First clear the directory?
        if ($clear) {
            rmdir_recursive($directory, false);
        }

        // File counter
        $total = count($this->xml->url);
        $count = 1;

        // Iterate through each url
        foreach($this->xml->url as $url) {
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
            $namespaces = $url->getNameSpaces(true);
            $rs = $url->children($namespaces['rs']);
            $hash = (string)$rs->md[0]->attributes()->hash;
            $algorithm = substr($hash, 0, strpos($hash, ':'));
            $cksum = substr($hash, strpos($hash, ':') + 1, (strlen($hash) - strlen($algorithm) - 1));

            // Does the file exist already?
            $exists = false;
            if (file_exists($build) && (! $force)) {
                // Does the checksum match?  If so, we can skip the download later
                $md5 = base64_encode(md5_file($build, true));
                $this->debug(' - File already exists, check the checksum:');
                if ($md5 == $cksum) {
                    $exists = true;
                    $this->debug('  - Checksum matches, skipping...');
                } else {
                    $this->debug('  - Checksum does not match, not skipping');
                }
            }

            // Check if file had been updated since last run?
            $lastmod = new DateTime($url->lastmod, new DateTimeZone("UTC"));
            if (($lastmod > $lastrun) && (! $exists)) {
                $this->debug(' - Downloading file: ' . $build);
                $start = microtime(true);
                http_get_save($url->loc, $build);
                $end = microtime(true);
                $filesize = filesize($build);
                $timer = $end - $start;
                $speed = $filesize / $timer;
                $this->debug('  - Filesize: ' . $filesize . ' Time: ' . $timer . ' Speed: ' . $speed);
            } else if ($exists) {

            } else {
                $this->debug('  - Skipping! lastmod: ' . $lastmod->format('Y-m-d H:i:s') .
                             ' is before last run: ' . $lastrun->format('Y-m-d H:i:s'));
            }

            // Checksum the file?
            if (($checksum) && (!$exists)) {
                $md5 = base64_encode(md5_file($build, true));
                $this->debug(' - Checking checksum: ' . $algorithm . ' with value of ' . $cksum);
                if ($md5 == $cksum) {
                    $this->debug('  - MD5 matched: Expected:' . $cksum . ' Actual:' . $md5);
                } else {
                    echo '  - ERROR MD5 mismatch: Expected:' . $cksum . ' Actual:' . $md5 . "\n";
                }
            }
        }
    }

    // Whether to display debug messages or not
    function enableDebug($debug = true) {
        $this->debug = $debug;
    }

    // Display a debug mesage
    private function debug($message) {
        if ($this->debug) echo $message . "\n";
    }
}
?>