<?php
    // Whether or files can be opened directly
    $allow_url_fopen = ini_get('allow_url_fopen');

    // Whether curl can be used
    $allow_curl = function_exists('curl_init');

    function http_get($url) {
        global $allow_curl, $resync_delay;
        sleep($resync_delay);
        if ($allow_curl) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
            return $data;
        } else {
            return file_get_contents($url);
        }
    }

    function http_get_save($url, $filename) {
        global $allow_curl, $resync_delay;
        sleep($resync_delay);
        if ($allow_curl) {
            $fp = fopen($filename, 'w');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            $data = curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        } else {
            file_put_contents($filename, file_get_contents($url));
        }
    }

    function show_method() {
        global $allow_curl, $allow_url_fopen;
        if ($allow_curl && $allow_url_fopen) {
            echo " - allow_url_fopen and allow_curl set, defaulting to curl\n";
        } else if ($allow_curl) {
            echo " - allow_curl set, using curl\n";
        } else if ($allow_url_fopen) {
            echo " - allow_url_fopen set, using fopen\n";
        } else {
            die('allow_fopen_url not enabled, and curl not installed. Please enable or install one of these!');
        }
    }

?>