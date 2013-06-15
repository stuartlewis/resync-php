<?php
    // Test http options
    include 'util/http.php';
    echo "Testing http method used:\n";
    show_method();

    // Load config options
    include 'config/resync-config.php';
    include('ResyncURL.php');

    // Test discovery mechanism
    if (true) {
        include 'ResyncDiscover.php';
        $host = 'http://amazon.com/';
        $resyncdiscover = new ResyncDiscover($host);
        $capabilitylists = $resyncdiscover->getCapabilities();
        echo $host . ' - There were ' . count($capabilitylists) . ' capability lists found:' . "\n";
        foreach ($capabilitylists as $capabilitylist) {
            echo ' - ' . $capabilitylist . "\n";
        }
        $host = 'http://resync.library.cornell.edu/';
        $resyncdiscover = new ResyncDiscover($host);
        $capabilitylists = $resyncdiscover->getCapabilities();
        echo $host . ' - There were ' . count($capabilitylists) . ' capability lists found:' . "\n";
        foreach ($capabilitylists as $capabilitylist) {
            echo ' - ' . $capabilitylist . "\n";
        }
    }

    // Test the capability list feature
    if (true) {
        include('ResyncCapabilities.php');
        $url = 'http://resync.library.cornell.edu/arxiv-all/capabilitylist.xml';
        $resynccapabilities = new ResyncCapabilities($url);
        $capabilities = $resynccapabilities->getCapabilities();
        echo "\n" . 'Capabilities of ' . $url . "\n";
        foreach($capabilities as $capability => $type) {
            echo ' - ' . $capability . ' (capability type: ' . $type . ')' . "\n";
        }
        echo "\n";
    }

    if (true) {
        // Load a test resource list
        include 'ResyncResourcelist.php';
        $resourcelist = new ResyncResourcelist('http://resync.library.cornell.edu/arxiv-q-bio/resourcelist.xml');
        $resourcelist->registerCallback(function($file, $resyncurl) {
            echo '  - Callback given value of ' .$file . "\n";
            echo '   - XML:' . "\n" . $resyncurl->getXML()->asXML() . "\n";
        });
        $resourcelist->enableDebug();
        $resourcelist->baseline($resync_test_savedir);
        echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
             $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
        echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
             $resourcelist->getDownloadDuration() . ' seconds (' .
             ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";
    }

    if (true) {
        // Process a changelist
        include 'ResyncChangelist.php';
        $changelist = new ResyncChangelist('http://93.93.131.168:8080/rs/changelistarchive.xml');
        $changelist->registerCreateCallback(function($file, $resyncurl) {
            echo '  - CREATE Callback given value of ' .$file . "\n";
            echo '   - XML:' . "\n" . $resyncurl->getXML()->asXML() . "\n";
        });
        $changelist->registerUpdateCallback(function($file, $resyncurl) {
            echo '  - UPDATE Callback given value of ' .$file . "\n";
            echo '   - XML:' . "\n" . $resyncurl->getXML()->asXML() . "\n";
        });
        $changelist->registerDeleteCallback(function($file, $resyncurl) {
            echo '  - DELETE Callback given value of ' .$file . "\n";
            echo '   - XML:' . "\n" . $resyncurl->getXML()->asXML() . "\n";
        });
        $changelist->enableDebug();
        date_default_timezone_set('Europe/London');
        $since = new DateTime("2013-05-18 00:00:00.000000");
        $changelist->process($resync_test_savedir, $since);
        echo ' - ' . $changelist->getCreatedCount() . ' files created' . "\n";
        echo ' - ' . $changelist->getUpdatedCount() . ' files updated' . "\n";
        echo ' - ' . $changelist->getDeletedCount() . ' files deleted' . "\n";
        echo $changelist->getDownloadedFileCount() . ' files downloaded, and ' .
             $changelist->getSkippedFileCount() . ' files skipped' . "\n";
        echo $changelist->getDownloadSize() . 'Kb downloaded in ' .
             $changelist->getDownloadDuration() . ' seconds (' .
            ($changelist->getDownloadSize() / $changelist->getDownloadDuration()) . ' Kb/s)' . "\n";
        //print_r($changelist->getURLs());
    }
?>
