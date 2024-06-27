<?php

class Exporter {
    public static function exportCSV($detailed_ivods, $filepath) {
        $ivod_cnt = count($detailed_ivods);

        //headers
        $fp = fopen($filepath, 'w');
        $headers = [
            'ivod_id',
            'legislator_name',
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
            $fp = fopen($filepath, 'a');
            foreach ($csv_rows as $row) {
                fputcsv($fp, $row);
            }
            $csv_rows = null;
            fclose($fp);
        }
    }
}
