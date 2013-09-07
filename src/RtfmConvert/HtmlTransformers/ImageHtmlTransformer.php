<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class ImageHtmlTransformer extends AbstractHtmlTransformer {

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $qp->find('img[style="border: 0px solid black"]')
            ->removeAttr('style');
        $imageWrapper = $qp->find('span.image-wrap');
        $imageWrapper
            ->find('a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]')
            ->each(function ($index, $item) {
                $qp = qp($item);
                $href = $qp->attr('href');
                $href = str_replace('http://oldrtfm.modx.com', '', $href);
                $qp->attr('href', $href);

            });
        $qp->find('span.image-wrap[style=""]')
            ->contents()->unwrap();
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $pageData->addSimpleStat('img');
        $pageData->addSimpleStat('img[style="border: 0px solid black"]', true);

        $pageData->addSimpleStat('span.image-wrap');
        $pageData->addSimpleStat('span.image-wrap[style=""]', true);

        $pageData->addSimpleStat('a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]',
            true);
    }
}
