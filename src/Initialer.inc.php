<?php

class Initialer {
    public static function initalizeProject($term, $session_period) {
        if (! file_exists('json')) {
            mkdir('json');
        }
        if (! file_exists('csv')) {
            mkdir('csv');
        }
        $target_dir = "json/$term-$session_period";
        if (! file_exists($target_dir)) {
            mkdir($target_dir);
        }
        if (! file_exists("$target_dir/list")) {
            mkdir("$target_dir/list");
        }
        if (! file_exists("$target_dir/single")) {
            mkdir("$target_dir/single");
        }
    }
}
