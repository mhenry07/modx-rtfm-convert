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
        $pageData->beginTransform($qp);
        $expectedDiff = 0;

        if ($qp->firstChild()->is('p > br.atl-forced-newline:only-child')) {
            $expectedDiff -= 2;
            $qp->firstChild()->remove();
        }
        if ($qp->lastChild()->is('p > br.atl-forced-newline:only-child')) {
            $expectedDiff -= 2;
            $qp->lastChild()->remove();
        }
        $qp->find('br.atl-forced-newline')->removeAttr('class');

        $pageData->checkTransform('br.atl-forced-newline', $qp, $expectedDiff);
        return $qp;
    }

    // should I add stats for first & last?
    protected function generateStatistics(PageData $pageData) {
        $atlNewlines = $pageData->getHtmlQuery('br.atl-forced-newline');
        $pageData->addQueryStat('br.atl-forced-newline', $atlNewlines,
            array(self::TRANSFORM_ALL => true));
    }
}
