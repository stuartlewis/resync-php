<?php

include '../../config/resync-config.php';

?><html>
    <head>
        <title>ResourceSync web test</title>
    </head>
    <body>
    <h1>ResourceSync PHP Client Library</h1>

<?php
    // Test http options
    include '../../util/http.php';
    echo "<strong>Testing http method used:</strong> ";
    show_method();
    echo '<br /><br />';
    echo '<strong>Output:</strong><br />';

    // Load a test resource list
    include '../../ResyncResourcelist.php';
    $resourcelist = new ResyncResourcelist('http://resync.library.cornell.edu/arxiv-q-bio/resourcelist.xml');
    $resourcelist->enableDebug(true, true);

    // Baseline download the list (as at 1st Jan 1970)
    $date = new DateTime("1970-01-01T01:00:00Z", new DateTimeZone("UTC"));
    $resourcelist->baseline($resync_test_savedir, $date, false);
    echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
        $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
    echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
        $resourcelist->getDownloadDuration() . ' seconds (' .
        ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";
?>
    </body>
</html>