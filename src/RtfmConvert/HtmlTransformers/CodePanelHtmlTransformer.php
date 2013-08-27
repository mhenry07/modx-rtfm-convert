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
        $this->addCountStat('.code.panel', $matches, $isTransforming);
        $this->addCountStat('.code.panel .codeHeader',
            $matches->find('.codeHeader'), $isTransforming);
        $this->addCountStat('.code.panel pre:has(span[class^="code-"])',
            $matches->find('.code.panel pre')->has('span[class^="code-"]'),
            $isTransforming);
        $this->addCountStat('.code.panel pre:has(:not(span[class^="code-"]))',
            $matches->find('.code.panel pre')->has(':not(span[class^="code-"])'),
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

    /**
     * @param string $label
     * @param \QueryPath\DOMQuery $qp
     * @param bool $isTransforming
     * @param bool $warnIfFound
     */
    private function addCountStat($label, $qp, $isTransforming, $warnIfFound = false) {
        $count = $qp->count();
        $isWarning = $warnIfFound;
        if ($count === 0) {
            $isTransforming = false;
            $isWarning = false;
        }
        return $this->stats->add($label, $count, $isTransforming, $isWarning);
    }
}
