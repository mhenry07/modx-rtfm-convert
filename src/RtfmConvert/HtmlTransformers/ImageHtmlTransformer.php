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

        $qp->find('img[style="border: 0px solid black"]')
            ->removeAttr('style');
        $qp->find('img.emoticon[src="/images/icons/emoticons/smile.gif"]')
            ->replaceWith(':)');
        $qp->find('img.emoticon[src="/images/icons/emoticons/wink.gif"]')
            ->replaceWith(';)');
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $pageData->addSimpleStat('span.image-wrap');
        $pageData->addSimpleStat('span.image-wrap[style=""]', true);

        $pageData->addSimpleStat(
            'a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]',
            true);

        $pageData->addSimpleStat('img');
        $pageData->addSimpleStat('img[style="border: 0px solid black"]', true);

        $pageData->addSimpleStat('img.emoticon');

        $qp = $pageData->getHtmlQuery();
        $emoticonCount = $qp->find('img.emoticon')->count();
        if ($emoticonCount > 0) {
            $pageData->addSimpleStat(
                'img.emoticon[src="/images/icons/emoticons/smile.gif"]', true);
            $pageData->addSimpleStat(
                'img.emoticon[src="/images/icons/emoticons/wink.gif"]', true);

            $smileCount = $qp
                ->find('img.emoticon[src="/images/icons/emoticons/smile.gif"]')
                ->count();
            $winkCount = $qp
                ->find('img.emoticon[src="/images/icons/emoticons/wink.gif"]')
                ->count();
            $unhandledEmoticonCount = $emoticonCount - $smileCount - $winkCount;
            if ($unhandledEmoticonCount > 0)
                $pageData->addCountStat('img.emoticon (unhandled)',
                    $unhandledEmoticonCount, false, true);
        }
    }
}
