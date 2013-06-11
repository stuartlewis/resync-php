<?php

class ResyncURL {

    private $loc;

    private $xml;

    private $fileondisk;

    private $owner;

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

    function setOwner($owner) {
        $this->owner = $owner;
    }

    function getOwner() {
        return $this->owner;
    }

}