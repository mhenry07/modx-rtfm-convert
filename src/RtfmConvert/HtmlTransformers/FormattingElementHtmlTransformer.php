<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class FormattingElementHtmlTransformer extends AbstractHtmlTransformer {

    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $qp->find('font')->contents()->unwrap();
        $map = array(
            'b' => '<strong></strong>',
            'i' => '<em></em>',
            'tt' => '<code></code>');
        foreach ($map as $selector => $replace) {
            /** @var \QueryPath\DOMQuery $match */
            foreach ($qp->find($selector) as $match)
                $match->wrapInner($replace)->contents()->unwrap();
        }
        return $qp;
    }

    // TODO: stats for unhandled formatting elements
    protected function generateStatistics(PageData $pageData) {
        $selectors = array('font', 'b', 'i');
        foreach ($selectors as $selector)
            $pageData->addSimpleStat($selector, true);
        $pageData->addSimpleStat('tt', true, true);

        // non-transformed
        $pageData->addSimpleStat('hr');
        $warnSelectors = array('del', 'ins');
        foreach ($warnSelectors as $selector)
            $pageData->addSimpleStat($selector, false, true);
    }
}
