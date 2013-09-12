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

        if ($qp->firstChild()->is('p > br.atl-forced-newline:only-child')) {
            $pageData->incrementStat($label, self::TRANSFORM, 1, 'removed first');
            $expectedDiff -= 2;
            $qp->firstChild()->remove();
        }
        if ($qp->lastChild()->is('p > br.atl-forced-newline:only-child')) {
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

    // should I add stats for first & last?
    protected function generateStatistics(PageData $pageData) {
        $atlNewlines = $pageData->getHtmlQuery('br.atl-forced-newline');
        $pageData->addQueryStat('br.atl-forced-newline', $atlNewlines);
    }
}
