<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


class CodePanelHtmlTransformer extends AbstractHtmlTransformer {
    public function find() {
        return $this->qp->find('.code.panel');
    }

    public function generateStatistics($isTransforming = false) {
        if (is_null($this->stats)) return;
        $matches = $this->find();
        $this->addSimpleStat('.code.panel', $isTransforming);
        $this->addSimpleStat('.code.panel .codeHeader', $isTransforming);
        $this->stats->addCountStat('.code.panel pre:has(span[class^="code-"])',
            $matches->find('.code.panel pre')->has('span[class^="code-"]')->count(),
            $isTransforming);
        $this->stats->addCountStat('.code.panel pre:has(:not(span[class^="code-"]))',
            $matches->find('.code.panel pre')->has(':not(span[class^="code-"])')->count(),
            false, true);
    }

    public function transform() {
        $this->generateStatistics(true);
        $codePanels = $this->find();
        $codePanels->find('div.codeHeader')
            ->wrapInner('<p></p>');
        $codePanels->find('pre.code-java')
            ->addClass('brush: php')->removeClass('code-java')
            ->find('span[class^="code-"]')->contents()->unwrap();
        $codePanels->find('div.codeHeader, div.codeContent')
            ->contents()->unwrap()->unwrap();
        return $this->qp;
    }
}
