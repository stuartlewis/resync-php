<?php

    include '../config/resync-config.php';

?><html>
    <head>
        <title>ResourceSync web test</title>

        <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon"/>
        <link rel="stylesheet" href="../assets/resourcesync.css" type="text/css"/>
    </head>
    <body>
        <img alt="ReSync logo" src="../assets/resync_logo.jpg"/>
        <h1>ResourceSync PHP Client Library</h1>
        Scripts to test the <a href="http://www.openarchives.org/rs/">ResourceSync</a> <a href="https://github.com/stuartlewis/resync-php">PHP Library</a>.
        <h2>Configuration settings:</h2>
        [Edit config/resync-config.php]
        <ul>
            <li><strong>Test files:</strong> <?=$resync_test_savedir?></li>
            <li><strong>Delay between downloading files:</strong> <?=$resync_delay?> seconds</li>
        </ul>

        <h2>Perform operations:</h2>
        <ul>
            <form action="./discover/" method="get">
                <li><strong>Discover ResourceSync endpoints:</strong> <input type="text" name="url" size="80" value="http://resync.library.cornell.edu/"/><input type="submit" /></li>
            </form>
            <form action="./capabilities/" method="get">
                <li><strong>Examine capabilities:</strong> <input type="text" name="url" size="80" value="http://resync.library.cornell.edu/arxiv-all/capabilitylist.xml"/><input type="submit" /></li>
            </form>
            <form action="./baseline/" method="get">
                <li><strong>Baseline sync:</strong> <input type="text" name="url" size="80" value="http://resync.library.cornell.edu/arxiv-q-bio/resourcelist.xml"/><input type="submit" /></li>
            </form>
            <form action="./changelist/" method="get">
                <li><strong>Process changes:</strong> <input type="text" name="url" size="80" value="http://resync.library.cornell.edu/arxiv/changelist.xml"/><input type="submit" /></li>
            </form>
        </ul>
    </body>
</html>