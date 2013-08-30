<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;
use RtfmConvert\ProcessorOperationInterface;

class ModxTagsToEntitiesTextTransformer implements
    TextTransformerInterface, ProcessorOperationInterface {

    /**
     * Transform MODX special characters to HTML entities. Specifically,
     * square brackets.
     * @param string $input The input string.
     * @return string The transformed string.
     */
    public function transform($input) {
        $patterns = array('/\[/', '/\]/');
        $replacements = array('&#91;', '&#93;');
        return preg_replace($patterns, $replacements, $input);
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