#!/bin/bash
term=11
session_period=1
export_project_dir="/home/tmonk/work/export_taiwan_legislator_transcript"
hf_project_dir="/home/tmonk/work/taiwan-legislator-transcript"
php $export_project_dir/main.php -t $term --sp $session_period 
cp $export_project_dir/csv/hf_$term-$session_period.csv $hf_project_dir/csv/$term-$session_period.csv
git -C $hf_project_dir add csv/$term-$session_period.csv
git -C $hf_project_dir commit -m "Daily update via script"
git -C $hf_project_dir push

# for cron
#0 22 * * * cd /home/tmonk/work/export_taiwan_legislator_transcript && ./cron.sh > ./log/log.txt 2>&1
