<?php

include_once('http.php');
include_once('file.php');

class ResyncResourcelist
{
    public $url;

    private $xml;

    private $debug = true;

    function __construct($url) {
        $this->url = $url;
    }

    function load() {
        $xmllist = http_get($this->url);
        $this->xml = simplexml_load_string($xmllist);
        if ($this->debug) print_r($this->xml);
    }

    function baseline($directory, $lastrun, $clear = false, $checksum = true) {
        // First clear the directory?
        if ($clear) {
            rmdir_recursive($directory, false);
        }

        // File counter
        $total = count($this->xml->url);
        $count = 1;

        // Iterate through each url
        foreach($this->xml->url as $url) {
            if ($this->debug) echo 'Processing file (' . $count++ . ' of ' . $total . '): ' . $url->loc ."\n";

            // Create the directory if required
            $parts = explode('/', substr($url->loc, 7));
            $build = $directory;
            for ($p = 0; $p < count($parts) - 1; $p++) {
                $build = $build . '/' . $parts[$p];
                if (!file_exists($build)) {
                    mkdir($build);
                    if ($this->debug) echo 'Adding directory: ' . $build . "\n";
                }
            }
            $build .= '/' . $parts[count($parts) - 1];

            // Check if file had been updated since last run?
            $lastmod = new DateTime($url->lastmod, new DateTimeZone("UTC"));
            if ($lastmod > $lastrun) {
                if ($this->debug) echo 'Downloading file: ' . $build . "\n";
                $start = microtime(true);
                http_get_save($url->loc, $build);
                $end = microtime(true);
                $filesize = filesize($build);
                $timer = $end - $start;
                $speed = $filesize / $timer;
                if ($this->debug) echo 'Filesize: ' . $filesize . ' Time: ' . $timer . ' Speed: ' . $speed . "\n";
            } else {
                if ($this->debug) echo 'Skipping! lastmod: ' . $lastmod->format('Y-m-d H:i:s') .
                                       ' is before last run: ' . $lastrun->format('Y-m-d H:i:s') . "\n";
            }

            // Checksum the file?
            $namespaces = $url->getNameSpaces(true);
            if ($checksum) {
                $rs = $url->children($namespaces['rs']);
                $hash = (string)$rs->md[0]->attributes()->hash;
                $algorithm = substr($hash, 0, strpos($hash, ':'));
                $value = substr($hash, strpos($hash, ':') + 1, (strlen($hash) - strlen($algorithm) - 1));
                $md5 = base64_encode(md5_file($build, true));
                if ($this->debug) echo 'Checking checksum: ' . $algorithm . ' with value of ' . $value . "\n";
                if ($md5 == $value) {
                    if ($this->debug) echo 'MD5 matched: Expected:' . $value . ' Actual:' . $md5 . "\n";
                } else {
                    echo 'ERROR MD5 mismatch: Expected:' . $value . ' Actual:' . $md5 . "\n";
                }
            }
        }
    }
}
