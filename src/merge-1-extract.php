<?php
/**
 * @author: Mike Henry
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmMerge\ConfluenceContentExtractor;

function extract1($config) {
    $dateString = date('Ymd\THi');
    $config['stats_file'] = sprintf($config['stats_file_format'], $dateString);
    unset($config['stats_file_format']);

    $extractor = new ConfluenceContentExtractor($config);
    $extractor->extractSiteContent();
}

$config = array(
    'base_dir' => 'C:/temp/modx/rtfm',
    'cache_dir' => 'C:/temp/cache',
    'stats_file_format' => 'C:/temp/modx/extract1-%s.json'
);

extract1($config);
