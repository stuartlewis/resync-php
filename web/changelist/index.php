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

    // Load a test changelist
    include '../../ResyncChangelist.php';
    $changelist = new ResyncChangelist($_GET['url']);
    $changelist->enableDebug(true, true);
    $changelist->process($resync_test_savedir);
    echo ' - ' . $changelist->getCreatedCount() . ' files created' . "<br />";
    echo ' - ' . $changelist->getUpdatedCount() . ' files updated' . "<br />";
    echo ' - ' . $changelist->getDeletedCount() . ' files deleted' . "<br />";
    echo $changelist->getDownloadedFileCount() . ' files downloaded, and ' .
        $changelist->getSkippedFileCount() . ' files skipped' . "<br />";
    echo $changelist->getDownloadSize() . 'Kb downloaded in ' .
        $changelist->getDownloadDuration() . ' seconds (' .
        ($changelist->getDownloadSize() / $changelist->getDownloadDuration()) . ' Kb/s)' . "<br />";
?>
</body>
</html>