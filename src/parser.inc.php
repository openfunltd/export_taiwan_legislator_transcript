<?php

class Parser {
    public static function parseData($ivods) {
        $csvRows = [];
        foreach ($ivods as $idx => $ivod) {
            $row = [];
            $row[] = $ivod->id; //ivod id
            $row[] = self::getMeetName($ivod); //會議名稱（第11屆第1會期第17次會議）
            //事由（可以用精簡後的版本）
            $row[] = $ivod->date; //會議日期（2024-06-07）
            $row[] = substr($ivod->start_time, 11, 8); //發言結束時間（10:49:18）
            $row[] = substr($ivod->end_time, 11, 8); //發言開始時間（10:24:33）
            //公報章節名稱（ ivod API 內的 gazette > agenda > content ，可以知道這是報告事項還是國是論壇之類的）
            //發言內容
            break;
        }
    }

    private static function getMeetName($ivod) {
        $meetName = $ivod->會議名稱;
        $end_idx = mb_strpos($meetName, '（');
        $meetName = mb_substr($meetName, 0, $end_idx);
        return $meetName;
    }
}
