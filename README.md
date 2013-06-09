ResourceSync PHP Client Library
===============================
ResourceSync PHP Client Library
* http://www.openarchives.org/rs/

Test
----
Test by running 'php test/test.php' from the top-level resync-php directory.

Demo web implementation
-----------------------
Demo web interface can be enabled by deploying the resync-php directory to your PHP enabled web server.  Please note
that this is not a hardened web application, only a test interface for the library.  The web interface should therefore
not be deployed in a live server.

Basic library methods:
======================

Discovery
---------
```php
include('ResyncDiscover.php');
$resyncdiscover = new ResyncDiscover('http://resync.library.cornell.edu/');
$sitemaps = $resyncdiscover->getSitemaps();
echo ' - There were ' . count($sitemaps) . ' sitemaps found:' . "\n";
foreach ($sitemaps as $sitemap) {
    echo ' - ' . $sitemap . "\n";
}
```

Capability description
----------------------
```php
include('ResyncCapabilities.php');
$resynccapabilities = new ResyncCapabilities('http://resync.library.cornell.edu/arxiv/capabilitylist.xml');
$capabilities = $resynccapabilities->getCapabilities();
echo 'Capabilities' . "\n";
foreach($capabilities as $capability => $type) {
    echo ' - ' . $capability . ' (capability type: ' . $type . ')' . "\n";
}
```

Baseline (initial) synchronisation
----------------------------------
```php
include 'ResyncResourcelist.php';
$resourcelist = new ResyncResourcelist('http://resync.library.cornell.edu/arxiv/resourcelist.xml');
$resourcelist->enableDebug(); // Show progress
$resourcelist->baseline('/resync');
echo $resourcelist->getDownloadedFileCount() . ' files downloaded, and ' .
     $resourcelist->getSkippedFileCount() . ' files skipped' . "\n";
echo $resourcelist->getDownloadSize() . 'Kb downloaded in ' .
     $resourcelist->getDownloadDuration() . ' seconds (' .
    ($resourcelist->getDownloadSize() / $resourcelist->getDownloadDuration()) . ' Kb/s)' . "\n";
```

To baseline from a given date, use:

```php
$from = new DateTime("2013-05-18 00:00:00.000000");
$resourcelist->baseline('/resync', $from);
```

Changelist processing
---------------------
```php
include 'ResyncChangelist.php';
$changelist = new ResyncChangelist('http://resync.library.cornell.edu/arxiv/changelist.xml');
$changelist->enableDebug(); // Show progress
$changelist->process('/resync');
echo ' - ' . $changelist->getCreatedCount() . ' files created' . "\n";
echo ' - ' . $changelist->getUpdatedCount() . ' files updated' . "\n";
echo ' - ' . $changelist->getDeletedCount() . ' files deleted' . "\n";
echo $changelist->getDownloadedFileCount() . ' files downloaded, and ' .
     $changelist->getSkippedFileCount() . ' files skipped' . "\n";
echo $changelist->getDownloadSize() . 'Kb downloaded in ' .
     $changelist->getDownloadDuration() . ' seconds (' .
    ($changelist->getDownloadSize() / $changelist->getDownloadDuration()) . ' Kb/s)' . "\n";
```

To process changes from a given date, use:

```php
$from = new DateTime("2013-05-18 00:00:00.000000");
$changelist->process('/resync', $from);
```