<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;

use \QueryPath;
use RtfmConvert\PageStatistics;


class OldRtfmContentExtractor extends AbstractContentExtractor {

    /**
     * @param string $html
     * @param \RtfmConvert\PageStatistics $stats
     * @throws \RtfmConvert\RtfmException
     * @return string
     *
     * See notes from 8/26.
     */
    public function extract($html, PageStatistics $stats = null) {
        $qp = htmlqp($html, 'div.wiki-content');
        $this->generateDomStatistics($qp, $stats);
        if ($qp->count() === 0)
            throw new \RtfmConvert\RtfmException('Unable to locate div.wiki-content.');
        $qp->remove('script, style, div.Scrollbar');

        $content = '';
        /** @var \QueryPath\DOMQuery $item */
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

    private function generateDomStatistics(\QueryPath\DOMQuery $qp,
                                           PageStatistics $stats = null) {
        if (is_null($stats)) return;

        $isTransforming = true;
        $content = $qp->top('#content');

        // page metadata
        $stats->add('pageId', $content->find('#pageId')->attr('value'));
        $stats->add('pageTitle',
            $content->find('input[title="pageTitle"]')->first()->attr('value'));
        $stats->add('spaceKey', $content->find('#spaceKey')->attr('value'));
        $stats->add('spaceName',
            $content->find('input[title="spaceName"]')->first()->attr('value'));
        $modificationInfo = $content
            ->find('.page-metadata .page-metadata-modification-info')
            ->first();
        $modificationInfo->remove('.noprint');
        $stats->add('modification-info', trim($modificationInfo->text()));

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

        // TODO: move nbsp stats to NbspTextTransformer
        $nbsp = html_entity_decode('&nbsp;', ENT_HTML401, 'UTF-8');
        $stats->addCountStat('entities: nbsp',
            substr_count($html, $nbsp));
    }
}
