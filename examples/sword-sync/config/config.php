<?php

// Location of SWORDv2 PHP library
// Can be retrieved from https://github.com/swordapp/swordappv2-php-library/
$sac_client_location = '../../../swordappv2-php-library/';

// How many seconds to pause between web requests to a server
$resync_delay = 0;

// Location of saved test files
$resync_test_savedir = '/resync';

// Location where to write temporary deposit files (appended to $resync_test_savedir, no directory separator (/) required)
$sword_deposit_temp = 'sword';

// Deposit URL of destination collection, username, and password
$sac_deposit_location = 'http://93.93.131.168:8080/swordv2/collection/123456789/13';
$sac_deposit_username = 'demo';
$sac_deposit_password = 'demo';

// ResourceSync changelist to sync from
$rs_changelist = 'http://93.93.131.168:8080/rs/changelistarchive.xml';



?>