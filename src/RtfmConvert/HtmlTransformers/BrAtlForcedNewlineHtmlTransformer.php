<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


class BrAtlForcedNewlineHtmlTransformer extends AbstractHtmlTransformer {

    // should I add stats for first & last?
    public function generateStatistics($isTransforming = false) {
        if (is_null($this->stats)) return;
        $this->addSimpleStat('br.atl-forced-newline', $isTransforming);
    }

    public function transform() {
        $this->generateStatistics(true);
        if ($this->qp->firstChild()->is('p > br.atl-forced-newline:only-child'))
            $this->qp->firstChild()->remove();
        if ($this->qp->lastChild()->is('p > br.atl-forced-newline:only-child'))
            $this->qp->lastChild()->remove();
        $this->qp->find('br.atl-forced-newline')->removeAttr('class');
        return $this->qp;
    }
}
