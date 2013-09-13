<?php
/**
 * @author: Mike Henry
 */

$root = dirname(__DIR__);
require "{$root}/vendor/autoload.php";

use RtfmConvert\OldRtfmPageConverter;

$space = 'revolution20';
$page = 'Tag+Syntax';
// TODO: disable .html extension before final conversion
$addHtmlExtension = true;

$data = "{$root}/data";
$tocDir = "{$root}/oldrtfm-toc";
$outputDir = "{$data}/convert";
$cacheDir = "{$data}/cache";
$textDir = "{$data}/text";

$dateString = date('Ymd\THi');
$statsFile = "{$outputDir}/stats-{$dateString}.json";


$converter = new OldRtfmPageConverter($cacheDir, $textDir);

//$converter->convertPage(
//    "http://oldrtfm.modx.com/display/{$space}/{$page}",
//    "{$data}/{$page}.converted.html");

$converter->convertAll($tocDir, $outputDir, $addHtmlExtension, $statsFile);
