<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageStatistics;

abstract class AbstractHtmlTransformer {
    protected $qp;
    protected $stats;

    public function __construct($html, PageStatistics $stats = null) {
        $this->qp = qp($html, 'body');
        $this->stats = $stats;
    }

    abstract public function generateStatistics($isTransforming = false);
    abstract public function transform();

    protected function addSimpleStat($selector, $isTransforming = false,
                                     $warnIfFound = false, $isRequired = false) {
        $this->stats->addCountStat($selector,
            $this->qp->find($selector)->count(),
            $isTransforming, $warnIfFound, $isRequired);
    }
}
