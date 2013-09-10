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

        $pageData->beginTransform($qp);
        $fonts = $qp->find('font');
        $expectedDiff = -$fonts->count();
        $fonts->contents()->unwrap();
        $pageData->checkTransform('font', $qp, $expectedDiff);

        $map = array(
            'b' => '<strong></strong>',
            'i' => '<em></em>',
            'tt' => '<code></code>');
        foreach ($map as $selector => $replace) {
            $pageData->beginTransform($qp);
            $qp->find($selector)->each(
                function ($index, $item) use ($replace) {
                    qp($item)->wrapInner($replace)->contents()->unwrap();
                }
            );
            $pageData->checkTransform($selector, $qp, 0);
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
