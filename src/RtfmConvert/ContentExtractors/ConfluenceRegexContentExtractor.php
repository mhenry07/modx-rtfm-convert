<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\ContentExtractors;


use RtfmConvert\PageStatistics;
use RtfmConvert\RtfmException;

class ConfluenceRegexContentExtractor extends AbstractContentExtractor {

    public function extract($html, PageStatistics $stats = null) {
        $this->checkForErrors($html, $stats);

        $contentPattern = '#<div class="wiki-content">\s*<!-- wiki content -->(.*?)</div>\s*<!--\s*<rdf:RDF\b#s';
        $matches = array();
        preg_match($contentPattern, $html, $matches);
        $content = $matches[1];

        $removePatterns = array(
            '#<script\b.*?</script>#s',
            '#<style\b.*?</style>#s',
            '#<div class="Scrollbar">(?:(?!<div\b).)*?</div>#s');
        $content = preg_replace($removePatterns, '', $content);
        return $content;
    }

    protected function checkForErrors($html, PageStatistics $stats = null) {
        if (strpos($html, '</body>') === false ||
            strpos($html, '</html>') === false)
            throw new RtfmException('Document appears to be corrupt. Missing end tag for body and/or html element.');

        // check for unmatched div tags which could be an indication of missing content
        $divOpenTags = preg_match_all('#<div\b#', $html);
        $divCloseTags = preg_match_all('#</div>#', $html);
        $diff = $divOpenTags - $divCloseTags;
        if ($divOpenTags !== $divCloseTags && isset($stats))
            $stats->addTransformStat('warning: unmatched div(s)', abs($diff),
                array(PageStatistics::WARN_IF_FOUND => true,
                    PageStatistics::WARNING_MESSAGES => 'unmatched div(s)'));
    }
}
