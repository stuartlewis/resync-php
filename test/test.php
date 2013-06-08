<?php
    // Test http options
    include 'util/http.php';
    echo "Testing http method used:\n";
    show_method();

    // Load config options
    include 'config/resync-config.php';

    // Test discovery mechanism
    if (true) {
        include 'ResyncDiscover.php';
        $host = 'http://amazon.com/';
        $resyncdiscover = new ResyncDiscover($host);
        $sitemaps = $resyncdiscover->getSitemaps();
        echo $host . ' - There were ' . count($sitemaps) . ' sitemaps found:' . "\n";
        foreach ($sitemaps as $sitemap) {
            echo ' - ' . $sitemap . "\n";
        }
        $host = 'http://resync.library.cornell.edu/';
        $resyncdiscover = new ResyncDiscover($host);
        $sitemaps = $resyncdiscover->getSitemaps();
        echo $host . ' - There were ' . count($sitemaps) . ' sitemaps found:' . "\n";
        foreach ($sitemaps as $sitemap) {
            echo ' - ' . $sitemap . "\n";
        }
    }

    // Test the capability list feature
    if (true) {
        include('ResyncCapabilities.php');
        $url = 'http://resync.library.cornell.edu/arxiv/capabilitylist.xml';
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
        $resourcelist->registerCallback(function($file) {
           echo '  - Callback given value of ' .$file . "\n";
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
        $changelist = new ResyncChangelist('http://resync.library.cornell.edu/arxiv-all/changelist.xml');
        $changelist->registerCreateCallback(function($file) {
            echo '  - CREATE Callback given value of ' .$file . "\n";
        });
        $changelist->registerUpdateCallback(function($file) {
            echo '  - UPDATE Callback given value of ' .$file . "\n";
        });
        $changelist->registerDeleteCallback(function($file) {
            echo '  - DELETE Callback given value of ' .$file . "\n";
        });
        $changelist->enableDebug();
        $changelist->process($resync_test_savedir);
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
