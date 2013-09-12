<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageStatistics;

class RegexTextTransformer extends AbstractTextTransformer {
    protected $pattern;
    protected $replacement;
    protected $statLabel;

    function __construct($pattern, $replacement, $statLabel = null) {
        $this->pattern = $pattern;
        $this->replacement = $replacement;
        $this->statLabel = $statLabel;
    }

    /**
     * @param string|\RtfmConvert\PageData $input The input string or page data.
     * @return string The transformed string.
     */
    public function transform($input) {
        $subject = is_string($input) ? $input : $input->getHtmlString();
        $result = preg_replace($this->pattern, $this->replacement, $subject,
            -1, $count);
        if (!is_null($this->statLabel) && is_object($input))
            $input->addTransformStat($this->statLabel, $count,
                array(PageStatistics::TRANSFORM_ALL => true));
        return $result;
    }
}
