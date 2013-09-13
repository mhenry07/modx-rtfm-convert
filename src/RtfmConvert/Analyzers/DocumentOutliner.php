<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

class DocumentOutliner  implements ProcessorOperationInterface {

    protected $prefix;
    protected $compareToPrefix;

    public function __construct($prefix = '', $compareToPrefix = null) {
        $this->prefix = $prefix;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    function process($pageData) {
        $outline = $this->getOutline($pageData);
        $options = array();
        if (isset($this->compareToPrefix))
            array_merge($options, $this->compare($pageData));
        if (count($outline) > 0 || count($options) > 0)
            $pageData->addValueStat($this->prefix . 'outline', $outline,
                $options);
        return $pageData;
    }

    /**
     * Note: this just uses headings and ignores divs, sections, etc.
     *
     * Using filterCallback since the standard QueryPath find was returning
     * headings out of order.
     * Not using is() since it returns true even if descendants match selector.
     *
     * @param \RtfmConvert\PageData $pageData
     * @return array
     */
    protected function getOutline(PageData $pageData) {
        $filterHeadings = function ($index, $item) {
            $headingTags = array('h1', 'h2', 'h3', 'h4', 'h5', 'h6');
            return in_array(qp($item)->tag(), $headingTags, true);
        };

        $qp = $pageData->getHtmlQuery();
        $headings = $qp->find('*')->filterCallback($filterHeadings);
        $outline = array();
        $headings->each(function ($index, $item) use (&$outline) {
            $qp = qp($item);
            $outline[] = $qp->tag() . '. ' . trim($qp->text());
        });
        return $outline;
    }

    protected function compare(PageData $pageData) {
        if (is_null($this->compareToPrefix))
            return array();
        $statsArray = $pageData->getStats()->getStats();
        $label1 = $this->compareToPrefix . 'outline';
        $label2 = $this->prefix . 'outline';
        $outline1 = array_key_exists($label1, $statsArray) ?
            $statsArray[$label1][PageStatistics::VALUE] : array();
        $outline2 = array_key_exists($label2, $statsArray) ?
            $statsArray[$label2][PageStatistics::VALUE] : array();
        $diff = array_merge(array_diff($outline1, $outline2),
            array_diff($outline2, $outline1));
        $diffCount = count($diff);
        if ($diffCount > 0)
            return array(PageStatistics::ERROR => $diffCount,
                PageStatistics::ERROR_MESSAGES =>
                'Page outlines do not match. This likely means content is missing.');
    }
}
