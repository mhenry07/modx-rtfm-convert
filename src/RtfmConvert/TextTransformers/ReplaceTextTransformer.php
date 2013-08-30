<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


class ReplaceTextTransformer extends AbstractTextTransformer {
    protected $search;
    protected $replace;
    protected $statLabel;

    function __construct($search, $replace, $statLabel = null) {
        $this->search = $search;
        $this->replace = $replace;
        $this->statLabel = $statLabel;
    }

    /**
     * @param string|\RtfmConvert\PageData $input The input string.
     * @return string The transformed string.
     */
    public function transform($input) {
        $subject = is_string($input) ? $input : $input->getHtmlString();
        $result = str_replace($this->search, $this->replace, $subject, $count);
        if (!is_null($this->statLabel) && is_object($input))
            $input->addCountStat($this->statLabel, $count, $count > 0);
        return $result;
    }
}
