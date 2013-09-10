<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class ExternalLinkHtmlTransformer extends AbstractHtmlTransformer {

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $pageData->beginTransform($qp);

        $qp->find('a.external-link')->each(function ($index, $item) {
            $qp = qp($item);
            $qp->removeClass('external-link');
            if ($qp->attr('rel') == 'nofollow')
                $qp->removeAttr('rel');
        });

        $pageData->checkTransform('a.external-link', $qp, 0);
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $externalLinks = $pageData->getHtmlQuery('a.external-link');
        $pageData->addQueryStat('a.external-link', $externalLinks,
            array(self::TRANSFORM_ALL => true));
    }
}
