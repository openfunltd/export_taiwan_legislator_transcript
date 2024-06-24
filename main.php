<?php
include 'src/initialer.inc.php';
include 'src/downloader.inc.php';

Initialer::initalizeProject();
Downloader::downloadIvods(11);
//$content = json_decode(file_get_contents('json/single/sample.json'));
