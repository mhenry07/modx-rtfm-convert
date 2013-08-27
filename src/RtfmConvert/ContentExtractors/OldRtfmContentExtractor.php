<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;

use \QueryPath;


class OldRtfmContentExtractor extends AbstractContentExtractor {

    /**
     * @param string $html
     * @throws \RtfmConvert\RtfmException
     * @return string
     *
     * See notes from 8/26.
     */
    public function extract($html) {
        $qp = htmlqp($html, 'div.wiki-content');
        $this->generateDomStatistics($qp, true);
        if ($qp->count() === 0)
            throw new \RtfmConvert\RtfmException('Unable to locate div.wiki-content.');
        $qp->remove('script, style, div.Scrollbar');

        $content = '';
        /** @var \QueryPath\DOMQuery $item */
        foreach ($qp->contents() as $item) {
            $content .= $qp->document()->saveHTML($item->get(0));
        }

        $this->generateTextStatistics($content, true);
        $content = $this->removeWikiContentComment($content);
        $content = $this->restoreNbspEntities($content);

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
     * @param string $content
     * @return string
     */
    private function restoreNbspEntities($content) {
        $nbsp = html_entity_decode('&nbsp;', ENT_HTML401, 'UTF-8');
        return str_replace($nbsp, '&nbsp;', $content);
    }

    private function generateDomStatistics(\QueryPath\DOMQuery $qp,
                                           $isTransforming = false) {
        if (is_null($this->stats)) return;
        $wikiContent = $qp->top()->find('div.wiki-content');
        $this->stats->addCountStat('div.wiki-content', $wikiContent->count(),
            $isTransforming, false, true);
        $this->stats->addCountStat('script',
            $wikiContent->find('script')->count(), $isTransforming);
        $this->stats->addCountStat('style',
            $wikiContent->find('style')->count(), $isTransforming);
        $this->stats->addCountStat('div.Scrollbar',
            $wikiContent->find('div.Scrollbar')->count(), $isTransforming);
    }

    private function generateTextStatistics($html, $isTransforming = false) {
        if (is_null($this->stats)) return;

        $wikiContentCommentCount = substr_count($html, '<!-- wiki content -->');
        $this->stats->addCountStat('comments: wiki content',
            $wikiContentCommentCount, $isTransforming);
        $this->stats->addCountStat('comments: others',
            substr_count($html, '<!--') - $wikiContentCommentCount,
            false, true);

        $nbsp = html_entity_decode('&nbsp;', ENT_HTML401, 'UTF-8');
        $this->stats->addCountStat('entities: nbsp',
            substr_count($html, $nbsp), $isTransforming);
    }
}
