resync-php
==========

ResourceSync PHP Client Library
* http://www.openarchives.org/rs/

Test by running 'php test/test.php' from the top-level resync-php directory.

Demo web interface can be enabled by deploying the resync-php directory to your PHP enabled web server.  Please note
that this is not a hardened web application, only a test interface for the library.  The web interface should therefore
not be deployed in a live server.

Basic library methods:

 Discovery
 ---------
 ```php
 $host = 'http://resync.library.cornell.edu/';
 $resyncdiscover = new ResyncDiscover($host);
 $sitemaps = $resyncdiscover->getSitemaps();
 echo $host . ' - There were ' . count($sitemaps) . ' sitemaps found:' . "\n";
 foreach ($sitemaps as $sitemap) {
     echo ' - ' . $sitemap . "\n";
 }
 ```

 Capability description
 ----------------------
 ```php
 include('ResyncCapabilities.php');
 $url = 'http://resync.library.cornell.edu/arxiv/capabilitylist.xml';
 $resynccapabilities = new ResyncCapabilities($url);
 $capabilities = $resynccapabilities->getCapabilities();
 echo 'Capabilities of ' . $url . "\n";
 foreach($capabilities as $capability => $type) {
     echo ' - ' . $capability . ' (capability type: ' . $type . ')' . "\n";
 }
 ```