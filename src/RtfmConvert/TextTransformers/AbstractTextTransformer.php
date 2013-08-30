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
     * @param string $input The input string.
     * @return string The transformed string.
     */
    abstract public function transform($input);

    /**
     * @param PageData $pageData
     * @return PageData
     */
    public function process(PageData $pageData) {
        return new PageData(
            $this->transform($pageData->getHtmlString()),
            $pageData->getStats()
        );
    }
}
