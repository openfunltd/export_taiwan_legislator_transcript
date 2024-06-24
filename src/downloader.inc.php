<?php

class Downloader {
    public static function downloadIvods($term) {
        $url = "https://ly.govapi.tw/ivod?term=$term";
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

    public static function paginationDownload($page, $url) {
        $url = $url . "&page=$page";
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($ch);
        curl_close($ch);
        file_put_contents("json/list/page$page.json", $res);
    }
}
