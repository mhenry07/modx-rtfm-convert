<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Infrastructure;


use RtfmConvert\PageStatistics;

interface PageLoaderInterface {
    function setStatsPrefix($prefix);
    function get($url, PageStatistics $stats = null);
    function getData($url, PageStatistics $stats = null);
}
