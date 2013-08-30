<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

abstract class AbstractHtmlTransformer implements ProcessorOperationInterface {
    protected $qp;
    protected $stats;

    public function __construct($html, PageStatistics $stats = null) {
        $this->qp = qp($html, 'body');
        $this->stats = $stats;
    }

    abstract public function transform();
    abstract protected function generateStatistics($isTransforming = false);

    /**
     * @param PageData $pageData
     * @return PageData
     */
    public function process($pageData) {
        $this->qp = $pageData->getHtmlQuery();
        $this->stats = $pageData->getStats();
        $qp = $this->transform();
        return new PageData($qp, $this->stats);
    }

    protected function addSimpleStat($selector, $isTransforming = false,
                                     $warnIfFound = false, $isRequired = false) {
        $this->stats->addCountStat($selector,
            $this->qp->find($selector)->count(),
            $isTransforming, $warnIfFound, $isRequired);
    }
}