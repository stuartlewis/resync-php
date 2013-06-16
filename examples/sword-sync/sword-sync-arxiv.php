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

// Start at the top of the tree
$dir = '/resync/resync.library.cornell.edu/arxiv/ftp';
processDirectory($dir);

function processDirectory($dir) {
    // Process downloaded files
    echo "\n- Processing directory:" . $dir . "\n";
    $tmp = '/resync/sword/tmp/';
    $handle = opendir($dir);
    while (false !== ($entry = readdir($handle))) {
        if (($entry != '.') && ($entry != '..')) {
            // Is it a file or a directory?
            if (is_dir($dir . '/' . $entry)) {
                processDirectory($dir . '/' . $entry);
            } else if (substr($entry, -4) === '.abs') {
                echo '  - File: ' . $entry . "\n";
                $related = substr($entry, 0, strlen($entry) - 4) . '.tar.gz';
                if (file_exists($dir . '/' . $related)) {

                    $title = false;
                    $abstract = false;
                    $thetitle = '';
                    $theabstract = '';
                    $slashcounter = 0;
                    $abs = fopen($dir . '/' . $entry, 'r');
                    while (!feof($abs)) {
                        $line = fgets($abs);
                        //echo $line;
                        if (substr($line, 0, 6) == 'arXiv:') {
                            $id = substr($line, 7);
                        } else if (substr($line, 0, 6) == 'Title:') {
                            $thetitle = substr($line, 7);
                            $title = true;
                        } else if (substr($line, 0, 8) == 'Authors:') {
                            $theauthors = substr($line, 9);
                            $title = false;
                        } else if ($title) {
                            $thetitle .= ' ' . $line;
                        } else if (substr($line, 0, 1) == '\\') {
                            $slashcounter++;
                            if ($slashcounter == 2) {
                                $abstract = true;
                            } else {
                                $abstract = false;
                            }
                        } else if ($abstract) {
                            $theabstract .= ' ' . $line;
                        }
                    }
                    fclose($abs);

                    $theauthors = trim(preg_replace('/\s+/', ' ', $theauthors));
                    $thetitle = trim(preg_replace('/\s+/', ' ', $thetitle));
                    $id = trim(preg_replace('/\s+/', ' ', $id));
                    $theabstract = trim(preg_replace('/\s+/', ' ', $theabstract));

                    echo '   - Author: ' . $theauthors . "\n";
                    echo '   - Title: ' . $thetitle . "\n";
                    echo '   - Identifier: ' . $id . "\n";
                    echo '   - Abstract: ' . $theabstract . "\n";

                    // Create the atom entry
                    // The location of the files
                    $test_dirin = 'atom_multipart';

                    // Create the test package
                    global $resync_test_savedir, $sword_deposit_temp, $sword, $sac_deposit_location, $sac_deposit_username, $sac_deposit_password;
                    $sac_deposit_location = 'http://93.93.131.168:8080/swordv2/collection/123456789/42';
                    $atom = new PackagerAtomTwoStep($resync_test_savedir, $sword_deposit_temp, '', '');
                    $atom->setTitle($thetitle);
                    $atom->addMetadata('creator', $theauthors);
                    $atom->addMetadata('abstract', $theabstract);
                    $atom->setIdentifier($id);
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

                    echo '   - Edit IRI:' . $edit_iri . "\n";
                    echo '   - Edit Media IRI:' . $edit_media . "\n";

                    echo '    - Related file: ' . $related . "\n";
                    @deleteDirectory($tmp);
                    @mkdir($tmp);
                    copy($dir . '/' . $related, $tmp . $related);

                    try {
                        system('gunzip ' . $tmp . $related);
                        system('tar -xf ' . $tmp . substr($related, 0 , strlen($related) - 3) . ' -C ' . $tmp);
                        @unlink($tmp . substr($related, 0 , strlen($related) - 3));

                        $tmphandle = opendir($tmp);
                        while (false !== ($file = readdir($tmphandle))) {
                            if (($file != '.') && ($file != '..')) {
                                if (is_file($tmp . $file)) {
                                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                    $mime = finfo_file($finfo, $tmp . $file);
                                    echo '       - File: ' . $tmp . $file . ' (' . $mime . ")\n";

                                    // Deposit file
                                    $deposit = $sword->addExtraFileToMediaResource($edit_media,
                                        $sac_deposit_username,
                                        $sac_deposit_password,
                                        '',
                                        $tmp . $file,
                                        $mime);
                                }
                            }
                        }
                        closedir($tmphandle);
                    } catch (Exception $e) {
                        // Skip any errors for now
                    }

                    // Complete the deposit
                    $deposit = $sword->completeIncompleteDeposit($edit_iri,
                        $sac_deposit_username,
                        $sac_deposit_password,
                        '');

                    echo "\n";
                } else {
                    echo "    - No related files. Skipping!\n";
                }
            }
        }
    }
    closedir($handle);
}

function deleteDirectory($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!deleteDirectory($dir.DIRECTORY_SEPARATOR.$item)) return false;
    }
    return rmdir($dir);
}

?>