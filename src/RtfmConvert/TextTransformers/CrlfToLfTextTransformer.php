<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


/**
 * Cleans up line endings from $input by converting CR+LF to LF.
 */
class CrlfToLfTextTransformer extends ReplaceTextTransformer {
    public function __construct() {
        parent::__construct("\r\n", "\n");
    }
}
