<?php

    // Recursively delete a directory
    function rmdir_recursive($dir, $rmself = true) {
        foreach(scandir($dir) as $file) {
            if ('.' === $file || '..' === $file) continue;
            if (is_dir("$dir/$file")) rmdir_recursive("$dir/$file");
            else unlink("$dir/$file");
        }
        if ($rmself) rmdir($dir);
    }
?>