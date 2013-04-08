<?php

include_once('util/http.php');
include_once('util/file.php');

class ResyncDiscover {

    // The base URL of the site
    public $url;

    // The discovery raw XML
    private $xml;

    // List of discovered feeds
    private $sitemaps;

    // Whether or not to display debug information
    private $debug = false;

    // Whether the debugging messages are for display in HTML or not
    private $htmldebug = false;

    // Try to download discovery information
    function __construct($url) {
        $this->sitemaps = array();

        // Check the URL finishes with a slash
        if (!(substr($url, - count($url)) === '/')) {
            $url .= '/';
        }

        $this->url = $url . '.well-known/resourcesync';
        $response = get_headers($url);
        if ($response[0] == 'HTTP/1.1 200 OK') {
            $xmllist = http_get($this->url);
            $this->xml = simplexml_load_string($xmllist);
            foreach ($this->xml->sitemap as $sitemap) {
                array_push($this->sitemaps, (String)$sitemap->loc);
            }
        } else {
            $this->xml = '';
        }
        if ($this->debug) print_r($this->sitemaps);
    }

    // Return the list of sitemaps
    function getSitemaps() {
        return $this->sitemaps;
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
            ob_flush();
        } else {
            echo "\n";
        }
    }
}
?>