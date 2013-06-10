<?php

class ResyncURL {

    private $loc;

    private $xml;

    private $fileondisk;

    function __construct($loc, $xml, $fileondisk) {
        $this->loc = $loc;
        $this->xml = $xml;
        $this->fileondisk = $fileondisk;
    }

    function getLoc() {
        return $this->loc;
    }

    function getXML() {
        return $this->xml;
    }

    function getFileOnDisk() {
        return $this->fileondisk;
    }

}