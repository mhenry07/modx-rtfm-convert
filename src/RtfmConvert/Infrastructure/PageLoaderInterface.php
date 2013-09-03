<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Infrastructure;


use RtfmConvert\PageStatistics;

interface PageLoaderInterface {
    function get($url, $obsoleteCacheFile = null, PageStatistics $stats = null);
    function getData($url, $obsoleteCacheFile = null, PageStatistics $stats = null);
}
