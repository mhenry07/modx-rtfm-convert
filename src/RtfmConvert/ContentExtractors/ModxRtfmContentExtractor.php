<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\ContentExtractors;


use RtfmConvert\PageStatistics;
use RtfmConvert\RtfmException;

class ModxRtfmContentExtractor extends AbstractContentExtractor {
    protected $statPrefix;
    protected $excludeChildPagesSection;

    public function __construct($statPrefix, $excludeChildPagesSection = true) {
        $this->statPrefix = $statPrefix;
        $this->excludeChildPagesSection = $excludeChildPagesSection;
    }

    /**
     * @param string $html
     * @param PageStatistics $stats
     * @throws RtfmException
     * @return string
     */
    public function extract($html, PageStatistics $stats = null) {
        $this->checkForErrors($html, $stats);
        $pattern = '/<!-- start content -->(.*)<!-- end content -->/is';
        $matches = array();
        if (!preg_match($pattern, $html, $matches) === 1)
            throw new RtfmException('Error extracting content');
        $content = $matches[1];
        if ($this->excludeChildPagesSection) {
            $pattern = '#<div class="section-header">\s*<h[23] id="children-section-title".*?</div>#is';
            $count = 0;
            $content = preg_replace($pattern, '', $content, 1, $count);
            if ($count > 0)
                $stats->addTransformStat(
                    $this->statPrefix . 'content extraction',
                    1,
                    array(
                        PageStatistics::TRANSFORM_ALL => true,
                        PageStatistics::TRANSFORM_MESSAGES => 'excluded Child Pages section'));
        }

        return trim($content);
    }

    protected function checkForErrors($html, PageStatistics $stats = null) {
        if (strpos($html, '</body>') === false ||
            strpos($html, '</html>') === false)
            throw new RtfmException('Document appears to be corrupt. Missing end tag for body and/or html element.');

        // check for unmatched div tags which could be an indication of missing content
        $divOpenTags = preg_match_all('#<div\b#', $html);
        $divCloseTags = preg_match_all('#</div>#', $html);
        $diff = $divOpenTags - $divCloseTags;
        if ($divOpenTags !== $divCloseTags && !is_null($stats))
            $stats->addTransformStat(
                $this->statPrefix . 'warning: unmatched div(s)',
                abs($diff),
                array(PageStatistics::WARN_IF_FOUND => true,
                    PageStatistics::WARNING_MESSAGES => 'unmatched div(s)'));
    }

    protected function getSubstringBetween($str, $startStr, $endStr) {
        $startPos = strpos($str, $startStr);
        if ($startPos === false)
            return false;
        $startPos += strlen($startStr);

        $endPos = strpos($str, $endStr, $startPos);
        if ($endPos === false)
            return false;

        $len = $endPos - $startPos;
        return substr($str, $startPos, $len);
    }
}
