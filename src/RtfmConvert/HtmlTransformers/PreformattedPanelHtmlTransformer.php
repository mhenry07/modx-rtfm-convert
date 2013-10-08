<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;

class PreformattedPanelHtmlTransformer extends AbstractHtmlTransformer {
    // note: using wrapInner inside each since it seems to cause issues with multiple matches
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        if ($qp->count() == 0)
            return $qp;
        $preformattedPanels = $qp->find('.preformatted.panel');

        $this->transformPreformattedHeaders('.preformatted.panel .preformattedHeader',
            $preformattedPanels->find('div.preformattedHeader'), $pageData);
        $this->transformPreformattedPanels('.preformatted.panel', $preformattedPanels, $pageData);

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        if (is_null($pageData->getStats())) return;
        $preformattedPanelPres = $pageData->getHtmlQuery('.preformatted.panel pre');
        if ($preformattedPanelPres->count() == 0)
            return;
        $unhandledChildren = $preformattedPanelPres->has('*');
        if ($unhandledChildren->count() > 0)
            $pageData->addQueryStat('.preformatted.panel pre:has(*)',
                $unhandledChildren,
                array(self::WARN_IF_FOUND => true,
                    self::WARNING_MESSAGES => 'pre contains unhandled children'));
    }

    protected function transformPreformattedHeaders($label, DOMQuery $codeHeaders,
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

    protected function transformPreformattedPanels($label, DOMQuery $panels,
                                                   PageData $pageData) {
        $transformFn = function (DOMQuery $query) use ($label, $pageData) {
            $query->each(function ($index, $item) use ($label, $pageData) {
                $panel = qp($item);
                $pre = $panel->find('pre');
                $msg = 'stripped divs';
                if ($pre->hasAttr('class')) {
                    $class = $pre->attr('class');
                    $pageData->incrementStat($label, self::WARNING, 1,
                        "pre has unhandled class: {$class}");
                }
                if ($panel->find('figcaption')->count() > 0) {
                    $panel->wrapInner('<figure class="code"></figure>');
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

        $expectedDiff = -2 * $panels->count() +
            $panels->find('figcaption')->count();
        $this->executeTransformStep($label, $panels, $pageData,
            $transformFn, $addStatFn, $expectedDiff);
    }
}
