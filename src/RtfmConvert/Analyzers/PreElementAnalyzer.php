<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

class PreElementAnalyzer implements ProcessorOperationInterface {
    protected $prefix;
    protected $compareToPrefix;

    function __construct($prefix = '', $compareToPrefix = null) {
        $this->prefix = $prefix;
        $this->compareToPrefix = $compareToPrefix;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $qp = $pageData->getHtmlQuery();
        $pres = $qp->find('pre');
        if ($pres->count() > 0)
            $pageData->addQueryStat($this->getLabel($this->prefix), $pres);
        if (isset($this->compareToPrefix))
            $this->compare($pageData, $pres->count());
        return $pageData;
    }

    public function compare(PageData $pageData, $preCount2) {
        $statsArray = $pageData->getStats()->getStats();
        $label1 = $this->getLabel($this->compareToPrefix);
        $preCount1 = array_key_exists($label1, $statsArray) ?
            $statsArray[$label1][PageStatistics::FOUND] : 0;
        if ($preCount2 != $preCount1) {
            $msg = 'Number of pre elements does not match. Content is missing.';
            echo 'Error: ', $msg, PHP_EOL;
            $pageData->incrementStat($this->getLabel($this->prefix),
                PageStatistics::ERROR, abs($preCount1 - $preCount2), $msg);
        }
    }

    protected function getLabel($prefix) {
        return $prefix . 'pre elements';
    }
}
