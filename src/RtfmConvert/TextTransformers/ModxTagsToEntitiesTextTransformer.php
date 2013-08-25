<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


class ModxTagsToEntitiesTextTransformer implements TextTransformerInterface {

    /**
     * Transform MODX special characters to HTML entities. Specifically,
     * square brackets.
     * @param string $input The input string.
     * @return string The transformed string.
     */
    function transform($input) {
        $patterns = array('/\[/', '/\]/');
        $replacements = array('&#91;', '&#93;');
        return preg_replace($patterns, $replacements, $input);
    }
}