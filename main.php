<?php
include 'src/initialer.inc.php';
include 'src/downloader.inc.php';
include 'src/parser.inc.php';

Initialer::initalizeProject();
Downloader::downloadIvods(11);
$ivods = Downloader::getIvodList();
Downloader::downloadIvodsDetail($ivods);
$detailed_ivods = Downloader::getDetailedIvodList();
$csvRows = Parser::parseData($detailed_ivods);
