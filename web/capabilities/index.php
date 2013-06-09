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
    include '../../ResyncCapabilities.php';
    $resynccapabilities = new ResyncCapabilities($_GET['url']);
    $capabilities = $resynccapabilities->getCapabilities();
    foreach($capabilities as $capability => $type) {?>
        <li><a href='<?=$capability?>'><?=$capability?></a> (capability type: <?=$type?><?php
            if ($type == 'resourcelist') {
                ?> [<a href='../baseline/index.php?url=<?=$capability?>'>baseline sync</a>]<?php
            } else if ($type == 'changelist') {
                ?> [<a href='../changelist/index.php?url=<?=$capability?>'>update</a>]<?php
            }
        ?>)</li><?php
    }?></ul>
    </body>
</html>