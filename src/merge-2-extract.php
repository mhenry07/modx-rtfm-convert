<?php
/**
 * @author: Mike Henry
 * Note: this covers steps 2 and 3
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmMerge\ModxFileContentExtractor;

function extract2($config) {
    $dateString = date('Ymd\THi');
    $config['stats_file'] = sprintf($config['stats_file_format'], $dateString);
    unset($config['stats_file_format']);

    $extractor = new ModxFileContentExtractor($config);
    $extractor->extractSiteContent();
}

$config = array(
    'base_dir' => 'C:/temp/modx/rtfm',
    'cache_dir' => 'C:/temp/cache',
    'stats_file_format' => 'C:/temp/modx/extract2-%s.json'
);

extract2($config);
