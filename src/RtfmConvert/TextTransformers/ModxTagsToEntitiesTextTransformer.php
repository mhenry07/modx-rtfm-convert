<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


class ModxTagsToEntitiesTextTransformer extends AbstractTextTransformer {

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
}