<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;

class CodePanelHtmlTransformer extends AbstractHtmlTransformer {

    /** @var callable $addStatFn */
    protected $addStatFn;

    function __construct() {
        $this->addStatFn = function ($label, DOMQuery $query,
                                        PageData $pageData) {
            $pageData->addQueryStat($label, $query,
                array(self::TRANSFORM_ALL => true));
        };
    }

    // note: using wrapInner inside each since it seems to cause issues with multiple matches
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $codePanels = $qp->find('.code.panel');

        $this->transformCodeSpans('.code.panel pre:has(span[class^="code-"])',
            $codePanels->find('pre.code-java'), $pageData);
        $this->transformCodeHeaders('.code.panel .codeHeader',
            $codePanels->find('div.codeHeader'), $pageData);
        $this->transformCodePanels('.code.panel', $codePanels, $pageData);

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        if (is_null($pageData->getStats())) return;
        $codePanelPres = $pageData->getHtmlQuery('.code.panel pre');
        $pageData->addQueryStat(
            '.code.panel pre:has(:not(span[class^="code-"]))',
            $codePanelPres->has(':not(span[class^="code-"])'),
            array(self::WARN_IF_FOUND => true));
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
            $pageData->addQueryStat($label, $query,
                array(self::TRANSFORM_ALL => true));
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

        $this->executeTransformStep($label, $codeHeaders, $pageData,
            $transformFn, $this->addStatFn, 0);
    }

    protected function transformCodePanels($label, DOMQuery $codePanels,
                                           PageData $pageData) {
        $transformFn = function (DOMQuery $query) {
            $query->find('pre.code-java')
                ->removeClass('code-java')->addClass('brush: php')->unwrap();
            $query->contents()->unwrap();
        };

        $expectedDiff = -$codePanels->count() * 2;
        $this->executeTransformStep($label, $codePanels, $pageData,
            $transformFn, $this->addStatFn, $expectedDiff);
    }
}
