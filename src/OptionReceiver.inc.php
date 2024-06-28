<?php

class OptionReceiver {
    public static function getOptions() {
        $options = getopt('t:', ['sp:', 'output_type:']);
        $term = (array_key_exists('t', $options)) ? intval($options['t']) : null;
        $session_period = (array_key_exists('sp', $options)) ? $options['sp'] : null;
        $output_type = (array_key_exists('output_type', $options)) ? $options['output_type'] : null;
        $err_msg = null;
    
        if (is_null($term) or is_null($session_period)) {
           $err_msg = "term(屆期) and session_period(會期) are required arguments.\nexample: php main.php -t 11 --sp 1 \n";
        }

        $valid_output_types = ['hf', 'comparison'];
        if (isset($output_type) and ! in_array($output_type, $valid_output_types)) {
            $err_msg = "Valid arguments of output_type are 'hf', 'comparison'\n";
        }

        return [$term, $session_period, $output_type, $err_msg];
    }
}
