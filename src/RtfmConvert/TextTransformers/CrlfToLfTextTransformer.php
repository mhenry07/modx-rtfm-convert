<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


class CrlfToLfTextTransformer extends AbstractTextTransformer {

    /**
     * Clean up line endings from $input by converting CR+LF to LF.
     * @param string $input The input string.
     * @return string The transformed string with carriage returns removed.
     */
    public function transform($input) {
        return preg_replace('/\r\n/', "\n", $input);
    }
}
