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

    public function __construct($prefix = '', $compareToPrefix = null) {
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
        if ($pres->count() > 0 || isset($this->compareToPrefix))
            $pageData->addQueryStat($this->getLabel($this->prefix), $pres);
        if (isset($this->compareToPrefix))
            $this->compare($pageData, $pres->count());
        return $pageData;
    }

    public function compare(PageData $pageData, $preCount2) {
        $stats = $pageData->getStats();
        $prefix1 = $this->compareToPrefix;
        $label1 = $this->getLabel($prefix1);
        $preCount1 = $stats->getStat($label1, PageStatistics::FOUND) ? : 0;
        if ($preCount2 != $preCount1) {
            $prefix2 = $this->prefix;
            $label2 = $this->getLabel($prefix2);
            $msg = "Number of pre elements does not match. Content is missing. {$prefix1}{$preCount1} {$prefix2}{$preCount2}";
            echo 'Error: ', $msg, PHP_EOL;
            $pageData->incrementStat($label2, PageStatistics::ERROR,
                abs($preCount1 - $preCount2), $msg);
        }
    }

    protected function getLabel($prefix) {
        return $prefix . 'pre elements';
    }
}
