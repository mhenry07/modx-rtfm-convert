<?php
/**
 * @author: Mike Henry
 */

$root = dirname(__DIR__);
require "{$root}/vendor/autoload.php";

use RtfmConvert\OldRtfmPageConverter;

$space = 'revolution20';
$page = 'Tag+Syntax';
$cacheDir = "{$root}/data/cache/";
$converter = new OldRtfmPageConverter($cacheDir);
$converter->convert(
    "http://oldrtfm.modx.com/display/{$space}/{$page}",
    "{$root}/data/{$page}.converted.html");
