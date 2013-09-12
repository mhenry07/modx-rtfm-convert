<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

/**
 * Class NestedListHtmlTransformer
 * @package RtfmConvert\HtmlTransformers
 *
 * Fixes improperly nested lists (ul > ul, ol > ol, ul > div, etc.).
 * But *not* ul > ol or ol > ul due to the way QueryPath parses them.
 *
 * oldrtfm notes:
 * QueryPath appears to convert ul > ol and ol > ul (nested) to ul + ol and
 * ol + ul (siblings), whereas Chrome and Firefox appear to keep them nested.
 * But, it looks like oldrtfm has no ol's inside of ul's (ol > ul). It has a
 * few ul's inside of ol's, but they all appear to be properly nested inside
 * of li's (ol > li > ul).
 * Regexes:
 *  ul ol: <ul\b[^>]*>(.(?!</ul>))*?<ol
 *  ol ul: <ol\b[^>]*>(.(?!</ol>))*?<ul
 *
 * There are only a few ul's with nested ul's with no previous li. E.g.
 * http://oldrtfm.modx.com/display/revolution20/How+to+Write+a+Good+Snippet
 * Warn and wrap in a new li. Regex: <ul\b[^>]*>[^<]*<ul
 *
 * There are no nested ol's with no previous li.
 * There appear to be no dl's or menu's.
 */
class NestedListHtmlTransformer extends AbstractHtmlTransformer {

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $label = 'lists: nested';
        $selector = 'ul > :not(li), ol > :not(li)';
        $qp = $pageData->getHtmlQuery();
        $pageData->addQueryStat($label, $qp->find($selector));
        $this->addWarnings($label, $pageData);
        $pageData->beginTransform($qp);
        $expectedDiff = 0;

        // re-query after each iteration since other matches may be modified
        // so we can't use the usual \QueryPath\DOMQuery::each() or foreach
        while ($qp->find($selector)->count() > 0) {
            $nestedElement = $qp->find($selector)->first();
            $tag = $nestedElement->tag();
            $prevLi = $nestedElement->prev('li');
            if ($prevLi->count() > 0) {
                $pageData->incrementStat($label, self::TRANSFORM, 1,
                    "moved {$tag} into previous li");
                $nestedElement->detach()->attach($prevLi);
            } else {
                $pageData->incrementStat($label, self::TRANSFORM, 1,
                    "wrapped {$tag} in new li");
                $expectedDiff++;
                $nestedElement->wrapAll('<li></li>');
            }
        }
        $pageData->checkTransform($label, $qp, $expectedDiff);

        return $qp;
    }

    protected function addWarnings($label, PageData $pageData) {
        $nestedOnlyChildList = $pageData->getHtmlQuery(
            'ul > ul:only-child, ol > ol:only-child');
        $nestedOnlyChildList->each(
            function ($index, \DOMNode $item) use ($label, $pageData) {
                $qp = qp($item);
                $tag = $qp->tag();
                $parentTag = $qp->parent()->tag();
                $pageData->incrementStat($label, self::WARNING, 1,
                    "found nested list which is the only-child of the containing list ({$tag} > {$parentTag})");
            }
        );
    }
}
