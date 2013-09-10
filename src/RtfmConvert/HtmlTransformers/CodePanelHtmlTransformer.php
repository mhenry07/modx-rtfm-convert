<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class CodePanelHtmlTransformer extends AbstractHtmlTransformer {

    // note: using wrapInner inside each since it seems to cause issues with multiple matches
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $codePanels = $qp->find('.code.panel');
        $codePanels->find('div.codeHeader')->each(
            function ($index, $item) {
                qp($item)->wrapInner('<p></p>');
            }
        );
        $codePanels->find('pre.code-java')
            ->addClass('brush: php')->removeClass('code-java')
            ->find('span[class^="code-"]')->contents()->unwrap();
        $codePanels->find('div.codeHeader, div.codeContent')
            ->contents()->unwrap()->unwrap();
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        if (is_null($pageData->getStats())) return;
        $codePanels = $pageData->getHtmlQuery('.code.panel');
        $pageData->addQueryStat('.code.panel', $codePanels,
            array(self::TRANSFORM_ALL => true));
        $pageData->addQueryStat('.code.panel .codeHeader',
            $codePanels->find('.codeHeader'), array(self::TRANSFORM_ALL => true));
        $codePanelPres = $codePanels->find('pre');
        $pageData->addQueryStat('.code.panel pre:has(span[class^="code-"])',
            $codePanelPres->has('span[class^="code-"]'),
            array(self::TRANSFORM_ALL => true));
        $pageData->addQueryStat(
            '.code.panel pre:has(:not(span[class^="code-"]))',
            $codePanelPres->has(':not(span[class^="code-"])'),
            array(self::WARN_IF_FOUND => true));
    }
}
