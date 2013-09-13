<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class BrAtlForcedNewlineHtmlTransformer extends AbstractHtmlTransformer {

    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $label = 'br.atl-forced-newline';
        $pageData->beginTransform($qp);
        $expectedDiff = 0;

        if ($this->isFirstElementEmptyPBr($pageData)) {
            $pageData->incrementStat($label, self::TRANSFORM, 1, 'removed first');
            $expectedDiff -= 2;
            $qp->firstChild()->remove();
        }
        if ($this->isLastElementEmptyPBr($pageData)) {
            $pageData->incrementStat($label, self::TRANSFORM, 1, 'removed last');
            $expectedDiff -= 2;
            $qp->lastChild()->remove();
        }
        $remaining = $qp->find('br.atl-forced-newline');
        if ($remaining->count() > 0)
            $pageData->incrementStat($label, self::TRANSFORM,
                $remaining->count(), 'removed class');
        $remaining->removeAttr('class');

        $pageData->checkTransform($label, $qp, $expectedDiff);
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $atlNewlines = $pageData->getHtmlQuery('br.atl-forced-newline');
        $pageData->addQueryStat('br.atl-forced-newline', $atlNewlines);
    }

    // QueryPath is() seems to return true in some cases when a descendant
    // matches the selector when it's a string. It should only match if the
    // element itself matches the selector.
    protected function isFirstElementEmptyPBr(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $firstBr = $qp->find('p > br.atl-forced-newline:only-child')->first();
        if ($firstBr->count() == 0)
            return false;
        return $qp->firstChild()->is($firstBr->parent()->get(0));
    }

    protected function isLastElementEmptyPBr(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $lastBr = $qp->find('p > br.atl-forced-newline:only-child')->last();
        if ($lastBr->count() == 0)
            return false;
        return $qp->lastChild()->is($lastBr->parent()->get(0));
    }
}
