<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\ProcessorOperationInterface;

abstract class AbstractHtmlTransformer implements ProcessorOperationInterface {
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
