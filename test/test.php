<?php
    // Test http options
    include 'util/http.php';
    echo "Testing http method used:\n";
    show_method();

    // Load config options
    include 'config/resync-config.php';

    // Test discovery mechanism
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

    // Load a test resource list
    include 'ResyncResourcelist.php';
    $resourcelist = new ResyncResourcelist('http://resync.library.cornell.edu/arxiv-q-bio/resourcelist.xml');
    $resourcelist->enableDebug();

    // Baseline download the list (as at 1st Jan 1970)
    $date = new DateTime("1970-01-01T01:00:00Z", new DateTimeZone("UTC"));
    $resourcelist->baseline($resync_test_savedir, $date, false);
    echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
         $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
    echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
         $resourcelist->getDownloadDuration() . ' seconds (' .
         ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";