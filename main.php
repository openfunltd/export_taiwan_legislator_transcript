<?php
include 'src/initialer.inc.php';
include 'src/downloader.inc.php';
include 'src/parser.inc.php';
include 'src/exporter.inc.php';

$term = 11;
$session_period = 1;
$output_type = 'comparison';
$skip_no_gazette = ($output_type != 'comparison');

//下列三個 process 建議分開執行，不然會出現記憶體被消耗光的 error
//因為不想浪費時間在解決記憶體的問題，所以建議先人工分開執行

//process1
Initialer::initalizeProject(); //建立 json 資料夾
Downloader::downloadIvods($term, $session_period); //下載 ivod 清單到 json/list/

//process2
$ivods = Downloader::getIvodList(); //透過 json/list/*.json 取得 ivod 清單
Downloader::downloadIvodsDetail($ivods); //下載單一 ivod 資料（with_gazette=1）到 /json/single

//process3
$detailed_ivods = Downloader::getDetailedIvodList($skip_no_gazette); //取得含公報資訊的 ivod 資料
Exporter::exportCSV($detailed_ivods, $output_type . "_$term-$session_period.csv", $output_type);
