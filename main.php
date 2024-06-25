<?php
include 'src/initialer.inc.php';
include 'src/downloader.inc.php';

Initialer::initalizeProject();
Downloader::downloadIvods(11);
Downloader::getIvodList();
