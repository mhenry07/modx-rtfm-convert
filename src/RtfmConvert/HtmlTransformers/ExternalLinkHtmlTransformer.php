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
        $qp->find('a.external-link')->each(function ($index, $item) {
            $qp = qp($item);
            $qp->removeClass('external-link');
            if ($qp->attr('rel') == 'nofollow')
                $qp->removeAttr('rel');
        });
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $pageData->addSimpleStat('a.external-link', true);
    }
}
