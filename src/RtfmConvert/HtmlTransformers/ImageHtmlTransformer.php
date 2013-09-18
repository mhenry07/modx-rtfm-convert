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
        $this->execFn = function ($label, $selector, DOMQuery $query,
                                   PageData $pageData, callable $transformFn,
                                   $diffPerMatch, $transformDescription) {
            $addStatFn = function ($label, DOMQuery $query, PageData $pageData)
                use ($transformDescription) {
                if ($query->count() > 0)
                    $pageData->incrementStat($label, self::TRANSFORM,
                        $query->count(), $transformDescription);
            };
            $matches = $query->find($selector);
            $expectedDiff = $matches->count() * $diffPerMatch;
            $this->executeTransformStep($label, $matches, $pageData,
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
        $execFn('a.confluence-thumbnail-link', $selector, $qp, $pageData,
            $transformFn, 0, "converted {$selector} to relative URL");

        $selector = 'span.image-wrap[style=""]';
        $transformFn = function (DOMQuery $query) {
            $query->contents()->unwrap();
        };
        $execFn('span.image-wrap', $selector, $qp, $pageData, $transformFn, -1,
            "stripped tag {$selector}");

        $selector = 'img[style="border: 0px solid black"]';
        $transformFn = function (DOMQuery $query) {
            $query->removeAttr('style');
        };
        $execFn('img', $selector, $qp, $pageData, $transformFn, 0,
            'removed attribute style="border: 0px solid black" from img');

        $selector = 'img.emoticon[src="/images/icons/emoticons/smile.gif"]';
        $transformFn = function (DOMQuery $query) {
            $query->replaceWith(':)');
        };
        $execFn('img.emoticon', $selector, $qp, $pageData, $transformFn, -1,
            'converted smile emoticon img to text :)');

        $selector = 'img.emoticon[src="/images/icons/emoticons/wink.gif"]';
        $transformFn = function (DOMQuery $query) {
            $query->replaceWith(';)');
        };
        $execFn('img.emoticon', $selector, $qp, $pageData, $transformFn, -1,
            'converted wink emoticon img to text ;)');

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $addStat = function ($selector) use ($pageData, $qp) {
            $pageData->addQueryStat($selector, $qp->find($selector));
        };
        $addStat('span.image-wrap');
        if ($qp->find('a.confluence-thumbnail-link')->count() > 0)
            $addStat('a.confluence-thumbnail-link');
        $addStat('img');
        if ($qp->find('img')->count() == 0)
            return;

        $addStat('img.emoticon');

        $emoticonCount = $qp->find('img.emoticon')->count();
        if ($emoticonCount == 0)
            return;

        $smiles = $qp->find('img.emoticon[src="/images/icons/emoticons/smile.gif"]');
        $winks = $qp->find('img.emoticon[src="/images/icons/emoticons/wink.gif"]');
        $unhandledEmoticonCount = $emoticonCount - $smiles->count() -
            $winks->count();
        if ($unhandledEmoticonCount > 0)
            $pageData->incrementStat('img.emoticon', self::WARNING,
                $unhandledEmoticonCount, 'found unhandled emoticon(s)');
    }
}
