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
        $resourcelist = new ResyncResourcelist('http://resync.library.cornell.edu/arxiv/resourcelist.xml');
        $resourcelist->enableDebug();

        // Baseline sync
        $resourcelist->baseline($resync_test_savedir);
        echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
             $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
        echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
             $resourcelist->getDownloadDuration() . ' seconds (' .
             ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";
    }
?>