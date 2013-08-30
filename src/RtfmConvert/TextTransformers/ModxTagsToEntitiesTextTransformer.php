<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;

/**
 * Transforms MODX special characters to HTML entities. Specifically,
 * square brackets.
 */
class ModxTagsToEntitiesTextTransformer extends ReplaceTextTransformer {
    public function __construct() {
        parent::__construct(array('[', ']'), array('&#91;', '&#93;'));
    }
}
