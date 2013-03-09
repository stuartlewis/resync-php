<?php

    $allow_url_fopen = ini_get('allow_url_fopen');

    function http_get($url) {
        global $allow_url_fopen;
        if ($allow_url_fopen) {
            return file_get_contents($url);
        } else {
            die('curl');
        }
    }

    function http_get_save($url, $filename) {
        global $allow_url_fopen;
        if ($allow_url_fopen) {
            file_put_contents($filename, file_get_contents($url));
        } else {
            die('curl');
        }
    }

    function show_method() {
        global $allow_url_fopen;
        if ($allow_url_fopen) {
            echo " - allow_url_fopen set\n";
        } else {
            echo " - allow_url_fopen not set, using curl\n";
        }
    }

?>