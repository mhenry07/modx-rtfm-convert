<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;

use \QueryPath;


class OldRtfmContentExtractor implements ContentExtractorInterface {

    /**
     * @param string $html
     * @throws \RtfmConvert\RtfmException
     * @return string
     *
     * See notes from 8/26.
     */
    public function extract($html) {
        $qp = htmlqp($html, 'div.wiki-content');
        if ($qp->count() === 0)
            throw new \RtfmConvert\RtfmException('Unable to locate div.wiki-content.');
        $qp->remove('script, style, div.Scrollbar');

        $content = '';
        /** @var \QueryPath\DOMQuery $item */
        foreach ($qp->contents() as $item) {
            $content .= $qp->document()->saveHTML($item->get(0));
        }

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
}
