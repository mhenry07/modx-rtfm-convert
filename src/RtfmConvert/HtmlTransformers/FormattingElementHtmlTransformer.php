<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


class FormattingElementHtmlTransformer extends AbstractHtmlTransformer {

    // TODO: stats for unhandled formatting elements
    public function generateStatistics($isTransforming = false) {
        if (is_null($this->stats)) return;
        $selectors = array('font', 'b', 'i');
        foreach ($selectors as $selector)
            $this->addSimpleStat($selector, $isTransforming);
        $this->addSimpleStat('tt', $isTransforming, true);

        // non-transformed
        $this->addSimpleStat('hr');
        $warnSelectors = array('del', 'ins');
        foreach ($warnSelectors as $selector)
            $this->addSimpleStat($selector, false, true);
    }

    public function transform() {
        $this->generateStatistics(true);
        $this->qp->find('font')->contents()->unwrap();
        $map = array(
            'b' => '<strong></strong>',
            'i' => '<em></em>',
            'tt' => '<code></code>');
        foreach ($map as $selector => $replace) {
            $this->qp->find($selector)->wrapInner($replace)
                ->contents()->unwrap();
        }
        return $this->qp;
    }
}