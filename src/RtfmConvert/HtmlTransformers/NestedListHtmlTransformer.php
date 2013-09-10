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
    protected $counts;

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
//        echo "Before:\n", $qp->html(), PHP_EOL;
        $this->counts = array('hasPrevLi' => 0, 'noPrevLi' => 0);
        $selector = 'ul > :not(li), ol > :not(li)';
        $pageData->beginTransform($qp);

        // re-query after each iteration since other matches may be modified
        // so we can't use the usual \QueryPath\DOMQuery::each() or foreach
        while ($qp->find($selector)->count() > 0) {
            $nestedElement = $qp->find($selector)->first();
            $prevLi = $nestedElement->prev('li');
            if ($prevLi->count() > 0) {
                $this->counts['hasPrevLi']++;
                $nestedElement->detach()->attach($prevLi);
            } else {
                $this->counts['noPrevLi']++;
                $nestedElement->wrapAll('<li></li>');
            }
        }
        $pageData->checkTransform('lists: nested w/o prev li', $qp,
            $this->counts['noPrevLi']);

        $this->generateStatistics($pageData);
        return $qp;
    }

    // TODO: make better use of new stats API
    protected function generateStatistics(PageData $pageData) {
        $pageData->addTransformStat('lists: nested w prev li',
            $this->counts['hasPrevLi'], array(self::TRANSFORM_ALL => true));
        $pageData->addTransformStat('lists: nested w/o prev li',
            $this->counts['noPrevLi'],
            array(self::TRANSFORM_ALL => true, self::WARN_IF_FOUND => true));
    }
}
