<?php

class Exporter {
    //supporting types: hg(huggingface), comparison(為了比較事由摘要)
    public static function exportCSV($detailed_ivods, $filepath, $output_type='hg') {
        //headers
        $fp = fopen($filepath, 'w');
        if ($output_type == 'comparison') {
            $headers = self::$headers_digest_comparison;
        } else {
            $headers = self::$headers_all;
        }
        fputcsv($fp, $headers);
        fclose($fp);

        $ivod_cnt = count($detailed_ivods);
        $batch_size = 100; //批次寫入
        for ($i=0; $i < $ivod_cnt; $i = $i + $batch_size) {
            $batch_detailed_ivods = array_slice($detailed_ivods, $i, $batch_size);
            if ($output_type == 'comparison') {
                $csv_rows = Parser::parseDigestComparison($batch_detailed_ivods);
            } else {
                $csv_rows = Parser::parseHuggingFaceData($batch_detailed_ivods);
            }
            echo sprintf("(%d/%d)Writing to csv file... %d ~ %d\n", $i, $ivod_cnt, $i, $i + $batch_size);
            $fp = fopen($filepath, 'a');
            foreach ($csv_rows as $row) {
                fputcsv($fp, $row);
            }
            $csv_rows = null;
            fclose($fp);
        }
    }

    private static $headers_all = [
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

    private static $headers_digest_comparison = [
        'ivod_id',
        'original_meet_name',
        'digested_subjects',
    ];
}
