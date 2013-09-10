<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

abstract class AbstractHtmlTransformer implements ProcessorOperationInterface {
    const TRANSFORM_ALL = PageStatistics::TRANSFORM_ALL;
    const WARN_IF_FOUND = PageStatistics::WARN_IF_FOUND;
    const WARN_IF_MISSING = PageStatistics::WARN_IF_MISSING;

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    abstract public function transform(PageData $pageData);
    abstract protected function generateStatistics(PageData $pageData);

    /**
     * @param PageData $pageData
     * @return PageData
     */
    public function process($pageData) {
        $qp = $this->transform($pageData);
        return new PageData($qp, $pageData->getStats());
    }
}
