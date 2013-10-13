<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\ContentExtractors;


use RtfmConvert\PageStatistics;
use RtfmConvert\RtfmException;

class ModxRtfmContentExtractor extends AbstractContentExtractor {
    protected $statPrefix;

    public function __construct($statPrefix) {
        $this->statPrefix = $statPrefix;
    }

    /**
     * @param string $html
     * @param PageStatistics $stats
     * @throws RtfmException
     * @return string
     */
    public function extract($html, PageStatistics $stats = null) {
        $contentStart = '<!-- start content -->';
        $contentEnd = '<!-- end content -->';

        $this->checkForErrors($html, $stats);
        $content = $this->getSubstringBetween($html, $contentStart, $contentEnd);
        if ($content === false)
            throw new RtfmException('Error extracting content');

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
                $this->statPrefix . ' warning: unmatched div(s)',
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
