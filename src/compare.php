<?php
/**
 * @author: Mike Henry
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmCompare\SiteComparer;

function compare($site1, $site2, $config) {
    $dateString = date('Ymd\THi');
    $config['stats_file'] = sprintf($config['stats_file_format'],
        $site1, $site2, $dateString);
    unset($config['stats_file_format']);

    $comparer = new SiteComparer($site1, $site2, $config);
    $comparer->compareSites();
}


$config = include dirname(__FILE__) . '/compare.config.php';

if ($argc != 3) {
    echo "compare.php: Perform page by page comparison between two MODX RTFM sites.\n";
    echo "Usage: php compare.php <SITE1> <SITE2>\n";
    echo "  e.g. php compare.php oldrtfm.modx.com rtfm.modx.com\n";
    exit(1);
}

for ($i = 1; $i < $argc; $i++)
    if (!array_key_exists($argv[$i], $config['sites']))
        exit("ERROR: unexpected SITE {$argv[$i]}\n");

compare($argv[1], $argv[2], $config);
