<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


class ReplaceTextTransformer extends AbstractTextTransformer {
    protected $search;
    protected $replace;

    function __construct($search, $replace) {
        $this->search = $search;
        $this->replace = $replace;
    }

    /**
     * @param string $input The input string.
     * @return string The transformed string.
     */
    public function transform($input) {
        return str_replace($this->search, $this->replace, $input);
    }
}
