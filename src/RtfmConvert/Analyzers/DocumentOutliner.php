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
        $this->compareToPrefix = $compareToPrefix;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $outline = $this->getOutline($pageData);
        $pageData->addValueStat($this->getLabel($this->prefix), $outline);
        if (isset($this->compareToPrefix))
            $this->compare($pageData, $outline);
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
            $tag = $qp->tag();
            $text = $this->collapseWhitespace($qp->text());
            $outline[] = "{$tag}. {$text}";
        });
        return $outline;
    }

    // don't use \s in preg_replace, as it may corrupt utf-8 characters, e.g. nbsp
    protected function collapseWhitespace($text) {
        $text = preg_replace('/[ \t\r\n]+/', " ", $text);
        return trim($text);
    }

    protected function compare(PageData $pageData, $outline2) {
        $stats = $pageData->getStats();
        $label1 = $this->getLabel($this->compareToPrefix);
        $outline1 = $stats->getStat($label1, PageStatistics::VALUE) ? : array();
        $diff = array_merge(array_diff($outline1, $outline2),
            array_diff($outline2, $outline1));
        $diffCount = count($diff);
        if ($diffCount > 0) {
            $msg = "Page outlines don't match. Content is probably missing.";
            echo 'Error: ', $msg, PHP_EOL;
            $label2 = $this->getLabel($this->prefix);
            $pageData->incrementStat($label2, PageStatistics::ERROR,
                $diffCount, $msg);
        }
    }

    protected function getLabel($prefix) {
        return $prefix . 'outline';
    }
}
