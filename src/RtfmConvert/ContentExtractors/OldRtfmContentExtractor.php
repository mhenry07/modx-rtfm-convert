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
        // preprocess HTML that causes issues with QueryPath
        $html = $this->escapeAtlassianTemplate($html);

        $qp = RtfmQueryPath::htmlqp($html, 'div.wiki-content');
        $this->generateDomStatistics($qp, $stats);
        if ($qp->count() === 0)
            throw new RtfmException('Unable to locate div.wiki-content.');
        $qp->remove('script, style, div.Scrollbar');

        $content = '';
        /** @var DOMQuery $item */
        foreach ($qp->contents() as $item) {
            $content .= $qp->document()->saveHTML($item->get(0));
        }

        $this->generateTextStatistics($content, $stats);
        $content = $this->removeWikiContentComment($content);

        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    private function removeWikiContentComment($content) {
        return str_replace('<!-- wiki content -->', '', $content);
    }

    /**
     * Note: If these stats are not found, sourcePageId will indicate a warning.
     * This may happen if scripts contain </ and/or there are self-closing
     * div's <div/>, either of which may cause div#content to be closed
     * prematurely due to the way QueryPath parses those.
     * escapeAtlassianTemplate() should take care of </ within scripts and
     * RtfmQueryPath::htmlqp should prevent self-closing div's from being
     * generated.
     */
    private function generateDomStatistics(DOMQuery $qp,
                                           PageStatistics $stats = null) {
        if (is_null($stats)) return;

        $isTransforming = true;
        $content = $qp->top('#content');

        // page metadata
        $pageId = $content->find('#pageId');
        $stats->add('sourcePageId', $pageId->attr('value'), false,
            $pageId->count() == 0);
        $stats->add('pageTitle',
            $content->find('input[title="pageTitle"]')->first()->attr('value'));
        $stats->add('confluenceSpaceKey', $content->find('#spaceKey')->attr('value'));
        $stats->add('confluenceSpaceName',
            $content->find('input[title="spaceName"]')->first()->attr('value'));
        $modificationInfo = $content
            ->find('.page-metadata .page-metadata-modification-info')
            ->first();
        $modificationInfo->remove('.noprint');
        $stats->add('source-modification-info', trim($modificationInfo->text()));

        // stats
        $wikiContent = $content->find('div.wiki-content');

        $stats->addCountStat('div.wiki-content', $wikiContent->count(),
            $isTransforming, false, true);
        $stats->addCountStat('script',
            $wikiContent->find('script')->count(), $isTransforming);
        $stats->addCountStat('style',
            $wikiContent->find('style')->count(), $isTransforming);
        $stats->addCountStat('div.Scrollbar',
            $wikiContent->find('div.Scrollbar')->count(), $isTransforming);
    }

    private function generateTextStatistics($html,
                                            PageStatistics $stats = null) {
        if (is_null($stats)) return;
        $isTransforming = true;

        $wikiContentCommentCount = substr_count($html, '<!-- wiki content -->');
        $stats->addCountStat('comments: wiki content',
            $wikiContentCommentCount, $isTransforming);
        $stats->addCountStat('comments: others',
            substr_count($html, '<!--') - $wikiContentCommentCount,
            false, true);
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
    private function escapeAtlassianTemplate($html) {
        $pattern = '/<script type="text\/x-template"((?:[^>](?!\/>))*)>((?:(?!<[\/]?script>).)+)<\/script>/s'; // (?!]]>)
        $callback = function ($matches) {
            $scriptAttributes = $matches[1];
            $scriptCdata = preg_replace('/<\//', '<\/', $matches[2]);
            return "<script{$scriptAttributes}>{$scriptCdata}</script>";
        };
        return preg_replace_callback($pattern, $callback, $html);
    }
}
