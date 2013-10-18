<?php
/**
 * @author: Mike Henry
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';


use RtfmConvert\MergedContentCleaner;

function cleanup($config) {
    $dateString = date('Ymd\THi');
    $config['stats_file'] = sprintf($config['stats_file_format'], $dateString);
    unset($config['stats_file_format']);

    $cleaner = new MergedContentCleaner($config);
    $cleaner->cleanSiteContent();
}

$config = array(
    'base_dir' => 'C:/temp/modx/rtfm',
    'stats_file_format' => 'C:/temp/modx/cleanup7-%s.json'
);

cleanup($config);
