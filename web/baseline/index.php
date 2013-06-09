<?php

include '../../config/resync-config.php';

?><html>
    <head>
        <title>ResourceSync web test</title>

        <link rel="shortcut icon" href="../../assets/favicon.ico" type="image/x-icon"/>
        <link rel="stylesheet" href="../../assets/resourcesync.css" type="text/css"/>
    </head>
    <body>
    <img alt="ReSync logo" src="../../assets/resync_logo.jpg"/>
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
    $resourcelist = new ResyncResourcelist($_GET['url']);
    $resourcelist->enableDebug(true, true);

    // Baseline download the list (as at 1st Jan 1970)
    $date = new DateTime("1970-01-01T01:00:00Z", new DateTimeZone("UTC"));
    $resourcelist->baseline($resync_test_savedir, $date, false);
    echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
        $resourcelist->getSkippedFileCount() . ' files skipped' . "<br />";
    echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
        $resourcelist->getDownloadDuration() . ' seconds (' .
        ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "<br />";
?>
    </body>
</html>