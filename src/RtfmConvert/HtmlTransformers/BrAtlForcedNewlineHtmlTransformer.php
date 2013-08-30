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
        if ($qp->firstChild()->is('p > br.atl-forced-newline:only-child'))
            $qp->firstChild()->remove();
        if ($qp->lastChild()->is('p > br.atl-forced-newline:only-child'))
            $qp->lastChild()->remove();
        $qp->find('br.atl-forced-newline')->removeAttr('class');
        return $qp;
    }

    // should I add stats for first & last?
    protected function generateStatistics(PageData $pageData) {
        $pageData->addSimpleStat('br.atl-forced-newline', true);
    }
}
