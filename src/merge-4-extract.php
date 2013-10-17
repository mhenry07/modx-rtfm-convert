<?php
/**
 * @author: Mike Henry
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmConvert\ConvertedContentExtractor;

function extract4($config) {
    $dateString = date('Ymd\THi');
    $config['stats_file'] = sprintf($config['stats_file_format'], $dateString);
    unset($config['stats_file_format']);

    $extractor = new ConvertedContentExtractor($config);
    $extractor->extractSiteContent();
}

$config = array(
    'base_dir' => 'C:/temp/modx/rtfm',
    'cache_dir' => 'C:/temp/cache',
    'stats_file_format' => 'C:/temp/modx/extract4-%s.json'
);

extract4($config);
