<?php
    // Test http options
    include 'util/http.php';
    echo "Testing http method used:\n";
    show_method();

    // Load a test resource list
    include 'ResyncResourcelist.php';
    $resourcelist = new ResyncResourcelist('http://resync.library.cornell.edu/arxiv-q-bio/resourcelist.xml');
    $resourcelist->enableDebug();

    // Baseline download the list (as at 1st Jan 1970)
    $date = new DateTime("1970-01-01T01:00:00Z", new DateTimeZone("UTC"));
    $resourcelist->baseline('/resync', $date, false);