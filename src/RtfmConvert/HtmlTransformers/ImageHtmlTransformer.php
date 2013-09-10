<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;

class ImageHtmlTransformer extends AbstractHtmlTransformer {
    protected $execFn;

    function __construct() {
        $this->execFn = function ($selector, DOMQuery $query,
                                   PageData $pageData, callable $transformFn,
                                   $diffPerMatch) {
            $addStatFn = function ($label, DOMQuery $query, PageData $pageData) {
                $pageData->addQueryStat($label, $query,
                    array(self::TRANSFORM_ALL => true));
            };
            $matches = $query->find($selector);
            $expectedDiff = $matches->count() * $diffPerMatch;
            $this->executeTransformStep($selector, $matches, $pageData,
                $transformFn, $addStatFn, $expectedDiff);
        };
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $execFn = $this->execFn;

        $selector = 'a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]';
        $transformFn = function (DOMQuery $query) {
            $query->each(function ($index, $item) {
                $qp = qp($item);
                $href = $qp->attr('href');
                $href = str_replace('http://oldrtfm.modx.com', '', $href);
                $qp->attr('href', $href);
            });
        };
        $execFn($selector, $qp, $pageData, $transformFn, 0);

        $selector = 'span.image-wrap[style=""]';
        $transformFn = function (DOMQuery $query) {
            $query->contents()->unwrap();
        };
        $execFn($selector, $qp, $pageData, $transformFn, -1);

        $selector = 'img[style="border: 0px solid black"]';
        $transformFn = function (DOMQuery $query) {
            $query->removeAttr('style');
        };
        $execFn($selector, $qp, $pageData, $transformFn, 0);

        $selector = 'img.emoticon[src="/images/icons/emoticons/smile.gif"]';
        $transformFn = function (DOMQuery $query) {
            $query->replaceWith(':)');
        };
        $execFn($selector, $qp, $pageData, $transformFn, -1);

        $selector = 'img.emoticon[src="/images/icons/emoticons/wink.gif"]';
        $transformFn = function (DOMQuery $query) {
            $query->replaceWith(';)');
        };
        $execFn($selector, $qp, $pageData, $transformFn, -1);

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $addStat = function ($selector) use ($pageData, $qp) {
            $pageData->addQueryStat($selector, $qp->find($selector));
        };
        $addStat('span.image-wrap');
        $addStat('img');
        $addStat('img.emoticon');

        $emoticonCount = $qp->find('img.emoticon')->count();
        if ($emoticonCount > 0) {
            $smiles = $qp->find('img.emoticon[src="/images/icons/emoticons/smile.gif"]');
            $winks = $qp->find('img.emoticon[src="/images/icons/emoticons/wink.gif"]');
            $unhandledEmoticonCount = $emoticonCount - $smiles->count() -
                $winks->count();
            if ($unhandledEmoticonCount > 0)
                $pageData->addTransformStat('img.emoticon (unhandled)',
                    $unhandledEmoticonCount,
                    array(self::WARN_IF_FOUND => true));
        }
    }
}
