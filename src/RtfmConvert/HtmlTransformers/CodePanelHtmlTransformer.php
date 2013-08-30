<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class CodePanelHtmlTransformer extends AbstractHtmlTransformer {

    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $codePanels = $qp->find('.code.panel');
        $codePanels->find('div.codeHeader')
            ->wrapInner('<p></p>');
        $codePanels->find('pre.code-java')
            ->addClass('brush: php')->removeClass('code-java')
            ->find('span[class^="code-"]')->contents()->unwrap();
        $codePanels->find('div.codeHeader, div.codeContent')
            ->contents()->unwrap()->unwrap();
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        if (is_null($pageData->getStats())) return;
        $qp = $pageData->getHtmlQuery();
        $pageData->addSimpleStat('.code.panel', true);
        $pageData->addSimpleStat('.code.panel .codeHeader', true);
        $pageData->addCountStat('.code.panel pre:has(span[class^="code-"])',
            $qp->find('.code.panel pre')->has('span[class^="code-"]')->count(),
            true);
        $pageData->addCountStat('.code.panel pre:has(:not(span[class^="code-"]))',
            $qp->find('.code.panel pre')->has(':not(span[class^="code-"])')->count(),
            false, true);
    }
}
