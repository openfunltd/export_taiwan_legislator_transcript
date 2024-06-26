<?php
include 'src/OptionReceiver.inc.php';
include 'src/Initialer.inc.php';
include 'src/Downloader.inc.php';
include 'src/Parser.inc.php';
include 'src/Exporter.inc.php';

[$term, $session_period, $output_type, $is_refresh, $err_msg] = OptionReceiver::getOptions();

if (isset($err_msg)) {
    echo $err_msg;
    exit;
}

$skip_no_gazette = ($output_type != 'comparison');

//下列三個 process 建議分開執行，不然會出現記憶體被消耗光的 error
//因為不想浪費時間在解決記憶體的問題，所以建議先人工分開執行

Initialer::initalizeProject($term, $session_period); //建立 json 資料夾
//process1
Downloader::downloadIvods($term, $session_period); //下載 ivod 清單到 json/list/

//process2
$ivods = Downloader::getIvodList($term, $session_period); //透過 json/list/*.json 取得 ivod 清單
Downloader::downloadIvodsDetail($ivods, $is_refresh); //下載單一 ivod 資料（with_gazette=1）到 /json/single

//process3
$detailed_ivods = Downloader::getDetailedIvodList($term, $session_period, $skip_no_gazette); //取得含公報資訊的 ivod 資料
Exporter::exportCSV($detailed_ivods, "csv/" . $output_type . "_$term-$session_period.csv", $output_type);
