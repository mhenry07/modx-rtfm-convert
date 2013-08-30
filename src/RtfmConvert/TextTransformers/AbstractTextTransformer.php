<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;
use RtfmConvert\ProcessorOperationInterface;

abstract class AbstractTextTransformer implements ProcessorOperationInterface {
    /**
     * @param string|PageData $input The input string or page data.
     * @return string The transformed string.
     */
    abstract public function transform($input);

    /**
     * @param PageData $pageData
     * @return PageData
     */
    public function process($pageData) {
        return new PageData($this->transform($pageData),
            $pageData->getStats());
    }
}
