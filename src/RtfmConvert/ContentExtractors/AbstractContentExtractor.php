<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

abstract class AbstractContentExtractor implements ProcessorOperationInterface {
    abstract public function extract($html, PageStatistics $stats = null);

    /**
     * @param PageData $pageData
     * @return PageData
     */
    function process(PageData $pageData) {
        return new PageData(
            $this->extract($pageData->getHtmlString(), $pageData->getStats()),
            $pageData->getStats()
        );
    }}
