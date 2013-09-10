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
        $qp = $pageData->getHtmlQuery();
        $pageData->addQueryStat('span.image-wrap', $qp->find('span.image-wrap'));
        $pageData->addQueryStat('span.image-wrap[style=""]',
            $qp->find('span.image-wrap[style=""]'),
            array(self::TRANSFORM_ALL => true));

        $pageData->addQueryStat(
            'a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]',
            $qp->find('a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]'),
            array(self::TRANSFORM_ALL => true));

        $pageData->addQueryStat('img', $qp->find('img'));
        $pageData->addQueryStat('img[style="border: 0px solid black"]',
            $qp->find('img[style="border: 0px solid black"]'),
            array(self::TRANSFORM_ALL => true));

        $emoticons = $qp->find('img.emoticon');
        $pageData->addQueryStat('img.emoticon', $emoticons);

        $emoticonCount = $emoticons->count();
        if ($emoticonCount > 0) {
            $smiles = $qp->find('img.emoticon[src="/images/icons/emoticons/smile.gif"]');
            $winks = $qp->find('img.emoticon[src="/images/icons/emoticons/wink.gif"]');
            $pageData->addQueryStat(
                'img.emoticon[src="/images/icons/emoticons/smile.gif"]',
                $smiles, array(self::TRANSFORM_ALL => true));
            $pageData->addQueryStat(
                'img.emoticon[src="/images/icons/emoticons/wink.gif"]', $winks,
                array(self::TRANSFORM_ALL => true));

            $unhandledEmoticonCount = $emoticonCount - $smiles->count() -
                $winks->count();
            if ($unhandledEmoticonCount > 0)
                $pageData->addTransformStat('img.emoticon (unhandled)',
                    $unhandledEmoticonCount,
                    array(self::WARN_IF_FOUND => true));
        }
    }
}
