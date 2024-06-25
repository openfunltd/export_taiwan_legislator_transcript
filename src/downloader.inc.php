<?php

class Downloader {
    public static function downloadIvods($term, $session_period) {
        $url = "https://ly.govapi.tw/ivod?term=$term&sessionPeriod=$session_period";
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($res);
        echo 'There are total ' . $data->total->value . " ivods within " . $data->total_page . " pages\n";
        file_put_contents('json/list/page1.json', $res);
        for ($page = 2; $page <= $data->total_page; $page++) {
            echo "Downloading page $page..." . "\n";
            self::paginationDownload($page, $url);
        }
    }

    public static function getIvodList() {
        $content = self::getPaginationIvodList('page1.json', false);
        $ivods = $content->ivods;
        for ($page = 2; $page <= $content->total_page; $page++) {
            $ivods = array_merge($ivods, self::getPaginationIvodList("page$page.json"));
        }
        return $ivods;
    }

    public static function downloadIvodsDetail($ivods) {
        $ivods = array_filter($ivods, function ($ivod) {
            return in_array('gazette', $ivod->features);
        });
        $total_cnt = count($ivods);
        $cnt = 0;
        foreach ($ivods as $ivod) {
            $cnt++;
            $ivod_id = $ivod->id;
            echo "($cnt/$total_cnt) Downloading detailed ivod data... ivod_id: $ivod_id" . "\n";
            self::downloadSingleIvod($ivod_id);
        }
    }

    public static function getDetailedIvodList() {
        $filenames = scandir('json/single/');
        $filenames = array_slice($filenames, 2);
        //$filenames = ['150839.json']; //測試確認單一 ivod 資料用
        $detailed_ivods = [];
        foreach ($filenames as $filename) {
            $filepath = "json/single/$filename";
            $content = json_decode(file_get_contents($filepath));
            $detailed_ivods[] = $content;
        }
        return $detailed_ivods;
    }

    private static function downloadSingleIvod($ivod_id) {
        $url = "https://ly.govapi.tw/ivod/$ivod_id?with_gazette=1";
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents("json/single/$ivod_id.json", $res);
    }

    private static function paginationDownload($page, $url) {
        $url = $url . "&page=$page";
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents("json/list/page$page.json", $res);
    }

    private static function getPaginationIvodList($filename, $justIvods = true) {
        $filepath = "json/list/$filename";
        $content = json_decode(file_get_contents($filepath));
        if ($justIvods) {
            return $content->ivods;
        }
        return $content;
    }
}
