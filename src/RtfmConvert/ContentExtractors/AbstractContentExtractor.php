<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


use RtfmConvert\PageStatistics;

abstract class AbstractContentExtractor {
    protected $stats;

    function __construct(PageStatistics $stats = null) {
        $this->stats = $stats;
    }

    abstract public function extract($html);
}
