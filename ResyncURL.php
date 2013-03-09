<?php

include_once('http.php');

class ResyncURL
{
    public $loc;

    public $lastmod;

    public $metadata = array();

    function __construct($l, $lm = '', $md = []) {
        $this->loc = $l;
        $this->lastmod = $lm;
        $this->metadata = $md;
    }

    function get() {
        return http_get($this->loc);
    }

}