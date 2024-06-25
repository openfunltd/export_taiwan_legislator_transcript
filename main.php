<?php
include 'src/initialer.inc.php';
include 'src/downloader.inc.php';
include 'src/parser.inc.php';

$term = 11;
$session_period = 1;
Initialer::initalizeProject();
Downloader::downloadIvods($term, $session_period);
$ivods = Downloader::getIvodList();
Downloader::downloadIvodsDetail($ivods);
$detailed_ivods = Downloader::getDetailedIvodList();
$ivod_cnt = count($detailed_ivods);

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
fclose($fp);

$batch_size = 100;
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
