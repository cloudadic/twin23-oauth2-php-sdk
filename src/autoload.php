<?php

date_default_timezone_set('UTC');

if (!function_exists('pr')) {

    function pr($array, $exit = true) {
        echo PHP_EOL;
        print_r($array);
        echo PHP_EOL;

        if ($exit) {
            exit;
        }
    }

}
