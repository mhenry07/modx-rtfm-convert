<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageStatistics;

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
            $input->addTransformStat($this->statLabel, $count,
                array(PageStatistics::TRANSFORM_ALL => true));
        return $result;
    }
}
