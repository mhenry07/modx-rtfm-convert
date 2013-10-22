<?php
/**
 * @author: Mike Henry
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmMerge\ModxWebContentExtractor;

function extract5($config) {
    $dateString = date('Ymd\THi');
    $config['stats_file'] = sprintf($config['stats_file_format'], $dateString);
    unset($config['stats_file_format']);

    $extractor = new ModxWebContentExtractor($config);
    $extractor->extractSiteContent();
}

$config = array(
    'cache_dir' => 'C:/temp/cache',
    'output_dir' => 'C:/temp/modx/rtfm',
    'stats_file_format' => 'C:/temp/modx/extract5-%s.json',
    'toc_dir' => dirname(__DIR__) . '/oldrtfm-toc',
    'url' => 'http://rtfm.modx.com'
);

extract5($config);
