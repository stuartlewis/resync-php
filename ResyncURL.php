<?php

class ResyncURL {

    private $loc;

    private $xml;

    function __construct($loc, $xml) {
        $this->loc = $loc;
        $this->xml = $xml;
    }

    function getLoc() {
        return $this->loc;
    }

    function getXML() {
        return $this->xml;
    }

}