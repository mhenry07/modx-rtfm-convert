<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class FormattingElementHtmlTransformer extends AbstractHtmlTransformer {

    // note: using wrapInner inside each since it seems to cause issues with multiple matches
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $qp->find('font')->contents()->unwrap();
        $map = array(
            'b' => '<strong></strong>',
            'i' => '<em></em>',
            'tt' => '<code></code>');
        foreach ($map as $selector => $replace) {
            $qp->find($selector)->each(
                function ($index, $item) use ($replace) {
                    qp($item)->wrapInner($replace)->contents()->unwrap();
                }
            );
        }
        return $qp;
    }

    // TODO: stats for unhandled formatting elements
    protected function generateStatistics(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $selectors = array('font', 'b', 'i');
        foreach ($selectors as $selector)
            $pageData->addQueryStat($selector, $qp->find($selector),
                array(self::TRANSFORM_ALL => true));
        $pageData->addQueryStat('tt', $qp->find('tt'),
            array(self::TRANSFORM_ALL => true, self::WARN_IF_FOUND => true));

        // non-transformed
        $pageData->addQueryStat('hr', $qp->find('hr'));
        $warnSelectors = array('del', 'ins');
        foreach ($warnSelectors as $selector)
            $pageData->addQueryStat($selector, $qp->find($selector),
                array(self::WARN_IF_FOUND => true));
    }
}
