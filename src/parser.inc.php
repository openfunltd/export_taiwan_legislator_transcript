<?php

class Parser {
    public static function parseData($ivods) {
        $csv_rows = [];
        foreach ($ivods as $idx => $ivod) {
            //echo "$idx parsing ivod_id: $ivod->id" . "\n";
            $row = [];
            $row[] = $ivod->id; //ivod id
            $row[] = self::getMeetName($ivod); //會議名稱（第11屆第1會期第17次會議）

            $subjects = self::getSubjects($ivod);
            $row[] = implode("\n", self::digestSubjects($subjects)); //事由（精簡後的版本）

            $row[] = $ivod->date; //會議日期（2024-06-07）
            $row[] = substr($ivod->start_time, 11, 8); //發言結束時間（10:49:18）
            $row[] = substr($ivod->end_time, 11, 8); //發言開始時間（10:24:33）
            $row[] = $ivod->gazette->agenda->content; //公報章節名稱（可以知道這是報告事項還是國是論壇之類的）
            $row[] = self::getBlockContents($ivod); //發言內容
            $csv_rows[] = $row;
        }
        return $csv_rows;
    }

    private static function getMeetName($ivod) {
        $meetName = $ivod->會議名稱;
        $end_idx = mb_strpos($meetName, '（');
        $meetName = mb_substr($meetName, 0, $end_idx);
        return $meetName;
    }

    private static function getBlockContents($ivod) {
        $blocks = $ivod->gazette->blocks;
        $block_contents = [];
        foreach ($blocks as $block) {
            $block_contents[] = implode('', $block);
        }
        $contents =  implode("\n", $block_contents);
        return $contents;
    }

    private static function getSubjects($ivod) {
        $meet_name = $ivod->會議名稱;
        $first_order_indexes = ['一、', '二、', '三、', '四、', '五、', '六、', '七、', '八、', '九、', '十、',
            '十一、', '十二、', '十三、', '十四、', '十五、', '十六、', '十七、', '十八、', '十九、', '二十、'];
        $content = self::parseReason(trim($meet_name));
        $with_first_order_index = mb_strpos($content, $first_order_indexes[0]) === 0;

        if (! $with_first_order_index) {
            $subjects = [];
            $subjects[] = $content;
        } else {
            $subjects = self::parseSubjects($content, $first_order_indexes);
        }

        return $subjects;
    }

    private static function parseReason($raw) {
        $start_idx = mb_strpos($raw, "（事由：");
        $end_idx = mb_strrpos($raw, "）");
        $content = mb_substr($raw, $start_idx + 4, $end_idx - ($start_idx + 4));
        $content = preg_replace('/【.*?】/', '', $content);
        $content = trim($content);
        return $content;
    }

    private static function parseSubjects($content, $first_order_indexes)
    {
        $subjects = [];
        $last_index = 0;
        foreach ($first_order_indexes as $order => $idx) {
            $current_idx_offset = mb_strlen($first_order_indexes[$order + 1]);
            $last_idx_offset = mb_strlen($idx);
            if ($order == 19) {
                //代表有可能該會會議要處理的事項超過 19 個，目前僅支援 19 個
                $subjects[] = trim(mb_substr($content, $last_index + $last_idx_offset));
            }
            $current_index = mb_strpos($content, $first_order_indexes[$order + 1]);

            // current_index 應該要是最上層索引編號的位置
            // 但有時會遇到「第十六條之『二、』」的「二、」被認為是索引的誤判
            // 所以特別用下列的 code 偵測誤判並跳過
            $previous_char = mb_substr($content, $current_index - 1, 1);
            while ($current_index !== false && ! in_array($previous_char, ["\n", ' '])) {
                $current_index = mb_strpos($content, $first_order_indexes[$order + 1], $current_index + $current_idx_offset);
                $previous_char = mb_substr($content, $current_index - 1, 1);
            }

            if (! $current_index) {
                $subjects[] = trim(mb_substr($content, $last_index + $last_idx_offset));
                break;
            }
            $subjects[] = trim(mb_substr($content, $last_index + $last_idx_offset, $current_index - ($last_index + $last_idx_offset)));
            $last_index = $current_index;
        }
        return $subjects;
    }

    private static function digestSubjects($subjects)
    {
        $digested_subjects = array_map(function ($subject) {
            $digest = self::getBillSubject($subject);
            if (! $digest) {
                $digest = self::getI12nSubject($subject);
            }
            return $digest;
        }, $subjects);
        $merged_subjects = [];
        foreach ($digested_subjects as $idx => $metadata) {
            if (! $metadata) {
                $merged_subjects[] = ['polyfill', $subjects[$idx]];
                continue;
            }
            $subject_type = $metadata[0];
            if ($subject_type == 'i12n') {
                $merged_subjects[] = $metadata;
            }
            if ($subject_type == 'bill') {
                $isMerged = false;
                foreach ($merged_subjects as &$existing_metadata) {
                    if ($existing_metadata[1] == $metadata[1]) {
                        $existing_metadata[3] = $existing_metadata[3] + $metadata[3];
                        $isMerged = true;
                        break;
                    }
                }
                if (! $isMerged) {
                    $merged_subjects[] = $metadata;
                }
            }
        }
        $digested_subjects = array_map(function ($metadata) {
            $subject_type = $metadata[0];
            if (in_array($subject_type, ['i12n', 'polyfill'])) {
                return $metadata[1];
            }
            if ($subject_type == 'bill') {
                $law = $metadata[1];
                $law_type = $metadata[2];
                $bill_cnt = $metadata[3];
                if ($bill_cnt == 1) {
                    $result = sprintf("審查「%s」%s草案", $law, $law_type);
                } else {
                    $result = sprintf("併案審查「%s」%s草案", $law, $law_type);
                }
                return $result;
            }
            return 'error';
        }, $merged_subjects);
        return $digested_subjects;
    }

    private static function getBillSubject($subject)
    {
        $keyword = '擬具';
        if (mb_strpos($subject, $keyword)) {
            $lines = explode("\n", $subject);
            $bill_cnt = 0;
            $law_raw = '';
            foreach ($lines as $line) {
                if (mb_strpos($line, $keyword)) {
                    $bill_cnt++;
                    $start_idx = mb_strpos($line, '「');
                    $end_idx = mb_strpos($line, '」');
                    $current_law_raw = mb_substr($line, $start_idx + 1, $end_idx - ($start_idx + 1));
                    //以提案中法條名稱字最少的那一個為準
                    if (mb_strlen($law_raw) == 0 || mb_strlen($law_raw) > mb_strlen($current_law_raw)) {
                        $law_raw = $current_law_raw;
                    }
                }
            }
            //擷取提案法條名稱中母法名稱
            $law = self::extractLawName($law_raw) ?? $law_raw;

            //辨認 commit 是全新、修正或增訂
            $isUpdate = mb_strpos($law_raw, '修正');
            $isAppend = mb_strpos($law_raw, '增訂');
            $law_type = '新法';
            if ($isUpdate) {
                $law_type = '修正';
            } else if ($isAppend) {
                $law_type = '增訂';
            }

            return ['bill', $law, $law_type, $bill_cnt];
        }
        return false;
    }

    private static function getI12nSubject($subject)
    {
        $keyword = '質詢';
        if (mb_strpos($subject, $keyword)) {
            return ['i12n', $subject];
        }
        return false;
    }

    private static function extractLawName($raw_text)
    {
        $law_end_idx1 = mb_strrpos($raw_text, '法');
        $law_end_idx2 = mb_strrpos($raw_text, '條例');
        $exception_end_idx1 = mb_strrpos($raw_text, '作法');
        $law_name = null;
        if ($law_end_idx1 && $law_end_idx1 != $exception_end_idx1 + 1) {
            $law_name = mb_substr($raw_text, 0, $law_end_idx1 + 1);
        } else if ($law_end_idx2) {
            $law_name = mb_substr($raw_text, 0, $law_end_idx2 + 2);
        }
        return $law_name;
    }
}
