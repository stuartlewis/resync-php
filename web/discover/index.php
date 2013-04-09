<?php

include '../../config/resync-config.php';

?><html>
    <head>
        <title>ResourceSync web test</title>
    </head>
    <body>
    <h1>ResourceSync PHP Client Library</h1>

<?php
    include '../../ResyncDiscover.php';
    include '../../ResyncCapabilities.php';
    $resyncdiscover = new ResyncDiscover($_GET['url']);
    $sitemaps = $resyncdiscover->getSitemaps();
?>
    There were <?=count($sitemaps)?> sitemaps found at
    <a href='<?=$resyncdiscover->getURL()?>'><?=$resyncdiscover->getURL()?></a>:<br />
    <ul>
    <?php foreach ($sitemaps as $sitemap) { ?>
        <li><a href='<?=$sitemap?>'><?=$sitemap?></a><ul><?php
            flush();
            ob_flush();
            $resynccapabilities = new ResyncCapabilities($sitemap);
            $capabilities = $resynccapabilities->getCapabilities();
            foreach($capabilities as $capability => $type) {?>
                <li><a href='<?=$capability?>'><?=$capability?></a> (capability type: <?=$type?>)</li><?php
            }
        ?></ul></li>
    <?php } ?>
    </ul>
    </body>
</html>