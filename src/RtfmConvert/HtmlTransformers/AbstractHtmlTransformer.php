<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
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

    /**
     * Executes a transform step including adding a stat, executing the
     * transformation and checking the number of elements added or removed by
     * the operation.
     *
     * @param string $label A unique label for the stat.
     * @param DOMQuery $query The QueryPath object that $addStatFn will base
     * its stat on and that $transformFn will modify.
     * @param PageData $pageData Contains the PageStatistics to be updated.
     * @param callable $transformFn Callback to execute the transformation.
     * Arguments: DOMQuery $query
     * @param callable $addStatFn Callback to add a stat.
     * Arguments: string $label, DOMQuery $query, PageData $pageData
     * @param int|callable $getExpectedElementDiff An expected count or a
     * callback to calculate the expected count. Arguments: DOMQuery $query
     */
    protected function executeTransformStep($label, DOMQuery $query,
                                            PageData $pageData,
                                            callable $transformFn,
                                            callable $addStatFn,
                                            $getExpectedElementDiff) {
        $pageData->beginTransform($query);

        $expectedElementDiff = 0;
        if (is_integer($getExpectedElementDiff))
            $expectedElementDiff = $getExpectedElementDiff;
        if (is_callable($getExpectedElementDiff))
            $expectedElementDiff = $getExpectedElementDiff($query);

        $addStatFn($label, $query, $pageData);

        $transformFn($query);

        $pageData->checkTransform($label, $query, $expectedElementDiff);
    }
}
