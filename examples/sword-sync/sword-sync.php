<?php

// Load the config file
include_once('config/config.php');

// Does the mapping file (file on disk -> DSpace handle) exist?
if (!file_exists('mapping.php')) {
    // The file doesn't exist, so create it, then run a baseline sync
    echo "- First run!\n";
    echo " - Creating mapping file: mapping.php\n";
    touch('mapping.php');

    // An array of metadata objects to process later
    $metadataitems = array();

    // An array of related objects to process later
    $objectitems = array();

    // Run the baseline sync
    echo " - Running baseline sync...\n";
    include_once('../../ResyncResourceList.php');
    $resourcelist = new ResyncResourcelist('http://93.93.131.168:8080/rs/resourcelist.xml');
    $resourcelist->registerCallback(function($file, $resyncurl) {
        // Work out if this is a metadata object or a file
        global $metadataitems, $objectitems;
        $type = 'metadata';
        $namespaces = $resyncurl->getXML()->getNameSpaces(true);
        if (!isset($namespaces['sm'])) $sac_ns['sm'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $lns = $resyncurl->getXML()->children($namespaces['rs'])->ln;
        $owner = '';
        foreach($lns as $ln) {
            if (($ln->attributes()->rel == 'describedby') && ($ln->attributes()->href != 'http://purl.org/dc/terms/')) {
                $type = 'object';
                $owner = $ln->attributes()->rel;
            }
        }

        echo ' - New file saved: ' .$file . "\n";
        echo '  - Type: ' . $type . "\n";

        if ($type == 'metadata') {
            $metadataitems[] = $resyncurl;
        } else {
            $objectitems[(string)$owner] = $resyncurl;
        }
    });
    //$resourcelist->enableDebug();
    $resourcelist->baseline($resync_test_savedir);
    //echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
    //    $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
    //echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
    //    $resourcelist->getDownloadDuration() . ' seconds (' .
    //    ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";

    // Process downloaded files
    echo "\n- Processing metadata files:\n";
    $counter = 0;
    foreach ($metadataitems as $item) {
        echo " - Item " . ++$counter . ' of ' . count($metadataitems) . "\n";
        echo "  - Metadata file: " . $item->getFileOnDisk() . "\n";
        $xml = simplexml_load_file($item->getFileOnDisk());
        $namespaces = $xml->getNameSpaces(true);
        if (!isset($namespaces['dc'])) $sac_ns['dc'] = 'http://purl.org/dc/terms/';
        if (!isset($namespaces['dcterms'])) $sac_ns['dc'] = 'http://purl.org/dc/elements/1.1/';
        $dc = $xml->children($namespaces['dc']);
        $dcterms = $xml->children($namespaces['dcterms']);
        $title = $dc->title[0];
        $id = $dc->identifier[0];
        $date = $dcterms->issued[0];
        echo "  - Metadata:\n";
        echo '   - Title: ' . $title . "\n";
        echo '   - Identifier: ' . $id . "\n";
        echo '   - Date: ' . $date . "\n";


        echo "\n";
    }
}

// Load the mapping file
include_once('mapping.php');



?>