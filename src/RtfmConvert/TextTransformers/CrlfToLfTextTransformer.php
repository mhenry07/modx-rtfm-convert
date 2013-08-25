<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


class CrlfToLfTextTransformer implements TextTransformerInterface {

    /**
     * Clean up line endings from $str by converting CR+LF to LF.
     * @param string $input The input string.
     * @return string The transformed string with carriage returns removed.
     */
    function transform($input) {
        return preg_replace('/\r\n/', "\n", $input);
    }
}
