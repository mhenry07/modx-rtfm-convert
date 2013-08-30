<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;
use RtfmConvert\ProcessorOperationInterface;

class CrlfToLfTextTransformer implements
    TextTransformerInterface, ProcessorOperationInterface {

    /**
     * Clean up line endings from $str by converting CR+LF to LF.
     * @param string $input The input string.
     * @return string The transformed string with carriage returns removed.
     */
    function transform($input) {
        return preg_replace('/\r\n/', "\n", $input);
    }

    /**
     * @param PageData $pageData
     * @return PageData
     */
    function process(PageData $pageData) {
        return new PageData(
            $this->transform($pageData->getHtmlString()),
            $pageData->getStats()
        );
    }
}
