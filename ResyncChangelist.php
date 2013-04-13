<?php

include_once('util/http.php');
include_once('util/file.php');

class ResyncCHangelist {

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

    // How long the downloads took (in seconds)
    private $downloadtimer = 0;

    // How large the downloads were (in kilobytes)
    private $downloadsize = 0;

    // Whether or not to display debug information
    private $debug = false;

    // Whether the debugging messages are for display in HTML or not
    private $htmldebug = false;

    // Create the new changelist
    function __construct($url) {
        $this->url = $url;
        $xmllist = http_get($this->url);
        $xml = simplexml_load_string($xmllist);
        $this->xml = $xml;
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