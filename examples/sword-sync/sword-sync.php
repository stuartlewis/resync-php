<?php

// Load the config file
include_once('config/config.php');

// Load and initialise the SWORD library
require($sac_client_location . 'swordappclient.php');
require($sac_client_location . 'packager_atom_twostep.php');
$sword = new SWORDAPPClient();
if (!is_dir($resync_test_savedir . '/' . $sword_deposit_temp)) {
    mkdir($resync_test_savedir . '/' . $sword_deposit_temp);
};

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
    include_once('../../ResyncResourcelist.php');
    $resourcelist = new ResyncResourcelist('http://93.93.131.168:8080/rs/resourcelist.xml');
    $resourcelist->registerCallback(function($file, $resyncurl) {
        // Work out if this is a metadata object or a file
        global $metadataitems, $objectitems;
        $type = 'metadata';
        $namespaces = $resyncurl->getXML()->getNameSpaces(true);
        if (!isset($namespaces['sm'])) $sac_ns['sm'] = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        $lns = $resyncurl->getXML()->children($namespaces['rs'])->ln;
        $key = '';
        $owner = '';
        foreach($lns as $ln) {
            if (($ln->attributes()->rel == 'describedby') && ($ln->attributes()->href != 'http://purl.org/dc/terms/')) {
                $type = 'object';
                $key = $resyncurl->getLoc();
                $owner = $ln->attributes()->href;
            }
        }

        echo ' - New file saved: ' .$file . "\n";
        echo '  - Type: ' . $type . "\n";

        if ($type == 'metadata') {
            $metadataitems[] = $resyncurl;
        } else {
            $objectitems[(string)$key] = $resyncurl;
            $resyncurl->setOwner($owner);
        }
    });
    //$resourcelist->enableDebug();
    $resourcelist->baseline($resync_test_savedir);
    //echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
    //    $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
    //echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
    //    $resourcelist->getDownloadDuration() . ' seconds (' .
    //    ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";

    // Horrible hack - don't ask! For some reason with this particular data set, traversing in order fails
    // at item 4.  Reverse the order, and it all works. Go figure!
    $metadataitems = array_reverse($metadataitems);

    // Process downloaded files
    echo "\n- Processing metadata files:\n";
    $counter = 0;
    foreach ($metadataitems as $item) {
        echo " - Item " . ++$counter . ' of ' . count($metadataitems) . "\n";
        echo "  - Metadata file: " . $item->getFileOnDisk() . "\n";
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        $xml = @simplexml_load_file($item->getFileOnDisk());
        foreach (libxml_get_errors() as $e) {
            //var_dump($e);
        }
        if (count(libxml_get_errors()) > 0) {
            // Something went wrong - perhaps this item doesn't exist any more?
            echo "  - RESOURCE NO LONGER EXISTS!\n";
            continue;
        }
        $namespaces = $xml->getNameSpaces(true);
        if (!isset($namespaces['dc'])) $sac_ns['dc'] = 'http://purl.org/dc/terms/';
        if (!isset($namespaces['dcterms'])) $sac_ns['dc'] = 'http://purl.org/dc/elements/1.1/';
        $dc = $xml->children($namespaces['dc']);
        $dcterms = $xml->children($namespaces['dcterms']);
        $title = $dc->title[0];
        $contributor = $dc->contributor[0];
        $id = $dc->identifier[0];
        $date = $dcterms->issued[0];
        echo '   - Location: ' . $item->getLoc() . "\n";
        echo '   - Author: ' . $contributor . "\n";
        echo '   - Title: ' . $title . "\n";
        echo '   - Identifier: ' . $id . "\n";
        echo '   - Date: ' . $date . "\n";

        // Create the atom entry
        // The location of the files
        $test_dirin = 'atom_multipart';

        // Create the test package
        $atom = new PackagerAtomTwoStep($resync_test_savedir, $sword_deposit_temp, '', '');
        $atom->setTitle($title);
        $atom->addMetadata('creator', $contributor);
        $atom->setIdentifier($id);
        $atom->setUpdated($date);
        $atom->create();

        // Deposit the metadata record
        $atomfilename = $resync_test_savedir . '/' . $sword_deposit_temp . '/atom';
        echo '  - About to deposit metadata: ' . $atomfilename . "\n";
        $deposit = $sword->depositAtomEntry($sac_deposit_location,
                                            $sac_deposit_username,
                                            $sac_deposit_password,
                                            '',
                                            $atomfilename,
                                            true);
        $edit_iri = $deposit->sac_edit_iri;
        $cont_iri = $deposit->sac_content_src;
        $edit_media = $deposit->sac_edit_media_iri;
        $statement_atom = $deposit->sac_state_iri_atom;
        $statement_ore = $deposit->sac_state_iri_ore;

        echo '   - Edit IRI:' . $edit_iri . "\n";
        echo '   - Edit Media IRI:' . $edit_media . "\n";

        // Find related files for this metadata record
        foreach($objectitems as $object) {
            if ((string)$object->getOwner() == (string)$item->getLoc()) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $object->getFileOnDisk());
                echo '    - Related object: ' . $object->getLoc() . "\n";
                echo '     - File: ' . $object->getFileOnDisk() . ' (' . $mime . ")\n";

                // Deposit file
                $deposit = $sword->addExtraFileToMediaResource($edit_media,
                                                               $sac_deposit_username,
                                                               $sac_deposit_password,
                                                               '',
                                                               $object->getFileOnDisk(),
                                                               $mime);
            }
        }

        // Complete the deposit
        $deposit = $sword->completeIncompleteDeposit($edit_iri,
                                                     $sac_deposit_username,
                                                     $sac_deposit_password,
                                                     '');

        echo "\n";
    }
}

// Load the mapping file
include_once('mapping.php');



?>