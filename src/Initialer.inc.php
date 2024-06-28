<?php

class Initialer {
    public static function initalizeProject() {
        if (! file_exists('json')) {
            mkdir('json');
        }
        if (! file_exists('json/list')) {
            mkdir('json/list');
        }
        if (! file_exists('json/single')) {
            mkdir('json/single');
        }
    }
}
