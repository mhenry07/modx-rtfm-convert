<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;

class CodePanelHtmlTransformer extends AbstractHtmlTransformer {
    protected $brushMappings = array(
        'code-html' => 'html',
        'code-java' => 'php',
        'code-javascript' => 'javascript',
        'code-php' => 'php',
        'code-sql' => 'sql',
        'code-xml' => 'xml'
    );

    // note: using wrapInner inside each since it seems to cause issues with multiple matches
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        if ($qp->count() == 0)
            return $qp;
        $codePanels = $qp->find('.code.panel');

        $this->transformCodeSpans('.code.panel pre:has(span[class^="code-"])',
            $codePanels->find('pre'), $pageData);
        $this->transformCodeHeaders('.code.panel .codeHeader',
            $codePanels->find('div.codeHeader'), $pageData);
        $this->transformFormatterErrors('.code.panel div.error',
            $codePanels->find('pre.code-php')->siblings('div.error'), $pageData);
        $this->transformCodePanels('.code.panel', $codePanels, $pageData);

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        if (is_null($pageData->getStats())) return;
        $codePanelPres = $pageData->getHtmlQuery('.code.panel pre');
        if ($codePanelPres->count() == 0)
            return;
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
                qp($item)->wrapInner('<figcaption></figcaption>')
                    ->contents()->unwrap();
            });
        };

        $addStatFn = function ($label, DOMQuery $query, PageData $pageData) {
            if ($query->count() > 0)
                $pageData->addQueryStat($label, $query,
                    array(self::TRANSFORM_ALL => true,
                        self::TRANSFORM_MESSAGES => 'extracted to figcaption'));
        };

        $this->executeTransformStep($label, $codeHeaders, $pageData,
            $transformFn, $addStatFn, 0);
    }

    protected function transformFormatterErrors($label,
                                                DOMQuery $formatterErrors,
                                                PageData $pageData) {
        $transformFn = function (DOMQuery $query) {
            $query->remove();
        };

        $addStatFn = function ($label, DOMQuery $query, PageData $pageData) {
            if ($query->count() > 0)
                $pageData->addQueryStat($label, $query,
                    array(self::TRANSFORM_ALL => true,
                        self::TRANSFORM_MESSAGES =>
                        'removed source-code formatter error(s)'));
        };

        $expectedDiff = -2 * $formatterErrors->count();
        $this->executeTransformStep($label, $formatterErrors, $pageData,
            $transformFn, $addStatFn, $expectedDiff);
    }

    protected function transformCodePanels($label, DOMQuery $codePanels,
                                           PageData $pageData) {
        $transformFn = function (DOMQuery $query) use ($label, $pageData) {
            $query->each(function ($index, $item) use ($label, $pageData) {
                $codePanel = qp($item);
                $pre = $codePanel->find('pre');
                $hasMapping = false;
                $msg = 'stripped divs';
                foreach ($this->brushMappings as $from => $to) {
                    if ($pre->hasClass($from)) {
                        $pre->removeClass($from)
                            ->addClass('brush:')->addClass($to);
                        $hasMapping = true;
                        $msg .= " & changed pre class from {$from} to 'brush: {$to}'";
                    }
                }
                if (!$hasMapping) {
                    $class = $pre->attr('class');
                    $pageData->incrementStat($label, self::WARNING, 1,
                        "pre has unhandled class: {$class}");
                }
                if ($codePanel->find('figcaption')->count() > 0) {
                    $codePanel->wrapInner('<figure class="code"></figure>');
                    $msg = "wrapped in figure.code & {$msg}";
                }
                $pageData->incrementStat($label, self::TRANSFORM, 1, $msg);
            });
            $query->find('pre')->unwrap();
            $query->contents()->unwrap();
        };

        $addStatFn = function ($label, DOMQuery $query, PageData $pageData) {
            $pageData->addQueryStat($label, $query);
        };

        $expectedDiff = -2 * $codePanels->count() +
            $codePanels->find('figcaption')->count();
        $this->executeTransformStep($label, $codePanels, $pageData,
            $transformFn, $addStatFn, $expectedDiff);
    }
}
