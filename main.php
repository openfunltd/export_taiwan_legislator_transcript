<?php
include 'src/initialer.inc.php';
include 'src/downloader.inc.php';
include 'src/parser.inc.php';

$term = 11;
$session_period = 1;

//下列三個 process 建議分開執行，不然會出現記憶體被消耗光的 error
//因為不想浪費時間在解決記憶體的問題，所以建議先人工分開執行

//process1
//Initialer::initalizeProject(); //建立 json 資料夾
//Downloader::downloadIvods($term, $session_period); //下載 ivod 清單到 json/list/

//process2
//$ivods = Downloader::getIvodList(); //透過 json/list/*.json 取得 ivod 清單
//Downloader::downloadIvodsDetail($ivods); //下載單一 ivod 資料（with_gazette=1）到 /json/single

//process3
$detailed_ivods = Downloader::getDetailedIvodList(); //取得含公報資訊的 ivod 資料
$ivod_cnt = count($detailed_ivods);

//將 parse 好的資料存入 term-session_period.csv 檔中
$fp = fopen("$term-$session_period.csv", 'w');
$headers = [
    'ivod_id',
    'meet_name',
    'meet_subjects',
    'meet_date',
    'speech_start_time',
    'speech_end_time',
    'gazette_agenda_content',
    'transcript',
];
fputcsv($fp, $headers);
fclose($fp);

$batch_size = 100; //批次寫入
for ($i=0; $i < $ivod_cnt; $i = $i + $batch_size) {
    $batch_detailed_ivods = array_slice($detailed_ivods, $i, $batch_size);
    $csv_rows = Parser::parseData($batch_detailed_ivods);
    echo sprintf("(%d/%d)Writing to csv file... %d ~ %d\n", $i, $ivod_cnt, $i, $i + $batch_size);
    $fp = fopen("$term-$session_period.csv", 'a');
    foreach ($csv_rows as $row) {
        fputcsv($fp, $row);
    }
    $csv_rows = null;
    fclose($fp);
}
