<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


interface TextTransformerInterface {
    /**
     * @param string $input The input string.
     * @return string The transformed string.
     */
    function transform($input);
}
