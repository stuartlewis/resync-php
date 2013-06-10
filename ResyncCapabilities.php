<?php

include_once('util/http.php');
include_once('util/file.php');

class ResyncCapabilities {

    // The URL of the capability list
    public $url;

    // The capability list's raw XML
    private $xml;

    // Array of capabilities
    private $capabilities;

    // Whether or not to display debug information
    private $debug = false;

    // Whether the debugging messages are for display in HTML or not
    private $htmldebug = false;

    // Examine capability list
    function __construct($url) {
        $capabilities = array();
        $this->url = $url;
        $xmllist = http_get($this->url);
        $this->xml = simplexml_load_string($xmllist);
        $namespaces = $this->xml->getNameSpaces(true);
        if (!isset($namespaces['sm'])) $sac_ns['sm'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $entries = $this->xml->children($namespaces['sm'])->url;
        foreach ($entries as $entry) {
            $loc = (String)$entry->loc;
            $rs = $entry->children($namespaces['rs']);
            $capability = (string)$rs->md[0]->attributes()->capability;
            $capabilities[$loc] = $capability;
        }
        $this->capabilities = $capabilities;
        if ($this->debug) print_r($this->capabilities);
    }

    // Return the list of capabilities
    function getCapabilities() {
        return $this->capabilities;
    }

    // Return the URL
    function getURL() {
        return $this->url;
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
            ob_flush();
        }
    }
}
?>