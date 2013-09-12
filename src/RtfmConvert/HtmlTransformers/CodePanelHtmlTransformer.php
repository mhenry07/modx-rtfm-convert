<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;

class CodePanelHtmlTransformer extends AbstractHtmlTransformer {

    // note: using wrapInner inside each since it seems to cause issues with multiple matches
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $codePanels = $qp->find('.code.panel');

        $this->transformCodeSpans('.code.panel pre:has(span[class^="code-"])',
            $codePanels->find('pre'), $pageData);
        $this->transformCodeHeaders('.code.panel .codeHeader',
            $codePanels->find('div.codeHeader'), $pageData);
        $this->transformCodePanels('.code.panel', $codePanels, $pageData);

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        if (is_null($pageData->getStats())) return;
        $codePanelPres = $pageData->getHtmlQuery('.code.panel pre');
        $unhandledChildren = $codePanelPres->has('*')
            ->not('span[class^="code-"]');
        if ($unhandledChildren->count() > 0)
            $pageData->addQueryStat(
                '.code.panel pre:has(*:not(span[class^="code-"]))',
                $unhandledChildren,
                array(self::WARN_IF_FOUND => true,
                    self::WARNING_MESSAGES => 'pre contains unhandled children'));
    }

    protected function transformCodeSpans($label, DOMQuery $codePanelPres,
                                          PageData $pageData) {
        $selector = 'span[class^="code-"]';
        $transformFn = function (DOMQuery $query) use ($selector) {
            $query->find($selector)->contents()->unwrap();
        };

        $addStatFn = function ($label, DOMQuery $query, PageData $pageData)
            use ($selector) {
            if ($query->count() > 0)
                $query = $query->has($selector);
            if ($query->count() > 0)
                $pageData->addQueryStat($label, $query,
                    array(self::TRANSFORM_ALL => true,
                        self::TRANSFORM_MESSAGES => 'stripped tags'));
        };

        $preCodeSpans = $codePanelPres->find($selector);
        $expectedElementDiff = -$preCodeSpans->count();
        $this->executeTransformStep($label, $codePanelPres, $pageData,
            $transformFn, $addStatFn, $expectedElementDiff);
    }

    protected function transformCodeHeaders($label, DOMQuery $codeHeaders,
                                            PageData $pageData) {
        $transformFn = function (DOMQuery $query) {
            $query->each(function ($index, $item) {
                qp($item)->wrapInner('<p></p>')->contents()->unwrap();
            });
        };

        $addStatFn = function ($label, DOMQuery $query, PageData $pageData) {
            if ($query->count() > 0)
                $pageData->addQueryStat($label, $query,
                    array(self::TRANSFORM_ALL => true,
                        self::TRANSFORM_MESSAGES => 'extracted to p'));
        };

        $this->executeTransformStep($label, $codeHeaders, $pageData,
            $transformFn, $addStatFn, 0);
    }

    protected function transformCodePanels($label, DOMQuery $codePanels,
                                           PageData $pageData) {
        $transformFn = function (DOMQuery $query) {
            $query->find('pre.code-java')
                ->removeClass('code-java')->addClass('brush: php');
            $query->find('pre.code-html')
                ->removeClass('code-html')->addClass('brush: php');
            $query->find('pre')->unwrap();
            $query->contents()->unwrap();
        };

        $addStatFn = function ($label, DOMQuery $query, PageData $pageData) {
            $pageData->addQueryStat($label, $query,
                array(self::TRANSFORM_ALL => true, self::TRANSFORM_MESSAGES =>
                    'stripped divs & changed pre class to "brush: php"'));
        };

        $expectedDiff = -$codePanels->count() * 2;
        $this->executeTransformStep($label, $codePanels, $pageData,
            $transformFn, $addStatFn, $expectedDiff);
    }
}
