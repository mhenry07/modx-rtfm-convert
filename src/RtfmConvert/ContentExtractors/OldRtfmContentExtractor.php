<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;

use QueryPath\DOMQuery;
use RtfmConvert\PageStatistics;
use RtfmConvert\RtfmException;
use RtfmConvert\RtfmQueryPath;


class OldRtfmContentExtractor extends AbstractContentExtractor {

    /**
     * @param string $html
     * @param PageStatistics $stats
     * @throws RtfmException
     * @return string
     *
     * See notes from 8/26.
     */
    public function extract($html, PageStatistics $stats = null) {
        $this->checkForErrors($html, $stats);
        // preprocess HTML that causes issues with QueryPath
        $html = $this->escapeAtlassianTemplates($html);

        $qp = RtfmQueryPath::htmlqp($html, 'div.wiki-content');
        $this->generateDomStatistics($qp, $stats);
        if ($qp->count() === 0)
            throw new RtfmException('Unable to locate div.wiki-content.');
        $qp->remove('script, style, div.Scrollbar');

        $content = $qp->innerXHTML();

        $content = $this->removeWikiContentComment($content, $stats);

        return $content;
    }

    /**
     * @param string $content
     * @param \RtfmConvert\PageStatistics $stats
     * @return string
     */
    protected function removeWikiContentComment($content,
                                                PageStatistics $stats = null) {
        $content = str_replace('<!-- wiki content -->', '', $content, $count);

        if (!is_null($stats)) {
            $stats->addTransformStat('comments: wiki content', $count,
                array(PageStatistics::TRANSFORM_ALL => true));
            $stats->addTransformStat('comments: others',
                substr_count($content, '<!--') - $count,
                array(PageStatistics::WARN_IF_FOUND => true));
        }
        return $content;
    }

    /**
     * Note: If these stats are not found, sourcePageId will indicate a warning.
     * This may happen if scripts contain </ and/or there are self-closing
     * div's <div/>, either of which may cause div#content to be closed
     * prematurely due to the way QueryPath parses those.
     * escapeAtlassianTemplates() should take care of </ within scripts and
     * RtfmQueryPath::htmlqp should prevent self-closing div's from being
     * generated.
     */
    protected function generateDomStatistics(DOMQuery $qp,
                                             PageStatistics $stats = null) {
        if (is_null($stats)) return;

        $content = $qp->top('#content');
        if ($qp->top('#content #pageId')->count() == 0) {
            $stats->addTransformStat('#content #pageId', 0,
                array(PageStatistics::WARN_IF_MISSING => true,
                    PageStatistics::WARNING_MESSAGES => '#pageId not found in #content. Attempting to search from body.'));
            $content = $qp->top('body');
        }

        // page metadata
        $pageId = $content->find('#pageId');
        $pageIdOptions = array();
        if ($pageId->count() == 0)
            $pageIdOptions = array(PageStatistics::WARNING => 1,
                PageStatistics::WARNING_MESSAGES => 'Unable to locate pageId');
        $stats->addValueStat('source: pageId', $pageId->attr('value'),
            $pageIdOptions);
        $stats->addValueStat('source: pageTitle',
            $content->find('input[title="pageTitle"]')->first()->attr('value'));
        $stats->addValueStat('source: spaceKey',
            $content->find('#spaceKey')->attr('value'));
        $stats->addValueStat('source: spaceName',
            $content->find('input[title="spaceName"]')->first()->attr('value'));
        $modificationInfo = $content
            ->find('.page-metadata .page-metadata-modification-info')
            ->first();
        $modificationInfo->remove('.noprint');
        $stats->addValueStat('source: modification-info',
            trim($modificationInfo->text()));

        // stats
        $wikiContent = $content->find('div.wiki-content');

        $stats->addQueryStat('div.wiki-content', $wikiContent,
            array(PageStatistics::TRANSFORM_ALL => true,
                PageStatistics::ERROR_IF_MISSING => true));
        $stats->addQueryStat('script', $wikiContent->find('script'),
            array(PageStatistics::TRANSFORM_ALL => true));
        $stats->addQueryStat('style', $wikiContent->find('style'),
            array(PageStatistics::TRANSFORM_ALL => true));
        $stats->addQueryStat('div.Scrollbar',
            $wikiContent->find('div.Scrollbar'),
            array(PageStatistics::TRANSFORM_ALL => true));
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
            $stats->addTransformStat('warning: unmatched div(s)', abs($diff),
                array(PageStatistics::WARN_IF_FOUND => true));
    }

    /**
     * QueryPath has issues parsing </ within a script tag.
     *
     * Neither wrapping in CDATA comments nor wrapping in HTML comments seems
     * to fix parsing issues.
     * Adding a backslash before the forward slash seems to work, and though it
     * may break templates, it should be easily reversible and they shouldn't
     * be in the final output anyways.
     * See http://mathiasbynens.be/notes/etago
     *
     * See atlassian.js AJS.template
     * According to http://robertclockedile.me/confluence/confluence/includes/js/template-renderer.js
     * there does not appear to be a good way to escape using braces since
     * AJS.renderTemplate only matches numeric values, \{\d+\} e.g. {1}
     * See also https://developer.atlassian.com/display/AUI/Template
     */
    protected function escapeAtlassianTemplates($html) {
        $pattern = '#<script type="text/x-template"((?:[^>](?!/>))*)>((?:(?!<[/]?script>).)+)</script>#s'; // (?!]]>)
        $callback = function ($matches) {
            $scriptAttributes = $matches[1];
            $scriptCdata = preg_replace('#</#', '<\/', $matches[2]);
            return "<script{$scriptAttributes}>{$scriptCdata}</script>";
        };
        return preg_replace_callback($pattern, $callback, $html);
    }
}
