<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


class RegexTextTransformer extends AbstractTextTransformer {
    protected $pattern;
    protected $replacement;

    function __construct($pattern, $replacement) {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
    }

    /**
     * @param string $input The input string.
     * @return string The transformed string.
     */
    public function transform($input) {
        return preg_replace($this->pattern, $this->replacement, $input);
    }
}
