<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


use RtfmConvert\PageStatistics;

abstract class AbstractContentExtractor {
    abstract public function extract($html, PageStatistics $stats = null);
}
