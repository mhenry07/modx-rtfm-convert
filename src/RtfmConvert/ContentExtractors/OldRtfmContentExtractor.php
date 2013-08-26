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
     * @return string
     *
     * See notes from 8/26.
     */
    public function extract($html) {
        $qp = htmlqp($html, 'div.wiki-content');
        $qp->remove('script, style');

        $content = '';
        /** @var \QueryPath\DOMQuery $item */
        foreach ($qp->contents() as $item) {
            $content .= $qp->document()->saveHTML($item->get(0));
        }

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
}
