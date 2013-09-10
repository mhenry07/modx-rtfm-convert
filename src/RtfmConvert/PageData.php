<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use QueryPath\DOMQuery;

class PageData {
    /** @var \QueryPath\DOMQuery|string */
    protected $html;

    /** @var PageStatistics */
    protected $stats;

    /**
     * @param \QueryPath\DOMQuery|string $html
     * @param PageStatistics $stats
     */
    public function __construct($html, PageStatistics $stats = null) {
        $this->html = $html;
        $this->stats = $stats;
    }

    /**
     * Note: if there are multiple matches, assumes they are contiguous
     * @return string Returns the HTML as a string. If the internal type
     * is a \QueryPath\DOMQuery, converts it to a string starting with the
     * current selector.
     * Old (foreach): $result .=  $this->html->document()->saveHTML($item->get(0));
     */
    public function getHtmlString() {
        if (is_string($this->html))
            return $this->html;
        return RtfmQueryPath::getHtmlString($this->html);
    }

    /**
     * @return string Returns the HTML document as a string. If the internal
     * type is a \QueryPath\DOMQuery, returns the whole document as a string.
     * Old: return $this->html->document()->saveHTML();
     */
    public function getHtmlDocument() {
        if (is_string($this->html))
            return $this->html;
        return RtfmQueryPath::getHtmlString($this->html->top());
    }

    /**
     * @param string $selector
     * @return \QueryPath\DOMQuery
     */
    public function getHtmlQuery($selector = 'body') {
        if (is_string($this->html))
            return RtfmQueryPath::htmlqp($this->html, $selector);
        return $this->html;
    }

    /**
     * @return PageStatistics
     */
    public function getStats() {
        return $this->stats;
    }

    /**
     * @see PageStatistics::addTransformStat()
     */
    public function addTransformStat($label, $found, array $options = array()) {
        if (is_null($this->stats))
            return;
        $this->stats->addTransformStat($label, $found, $options);
    }

    /**
     * @see PageStatistics::addQueryStat()
     */
    public function addQueryStat($label, DOMQuery $query,
                                 array $options = array()) {
        if (is_null($this->stats))
            return;
        $this->stats->addQueryStat($label, $query, $options);
    }

    /**
     * @see PageStatistics::beginTransform()
     */
    public function beginTransform(DOMQuery $query) {
        if (is_null($this->stats))
            return;
        $this->stats->beginTransform($query);
    }

    /**
     * @see PageStatistics::checkTransform()
     */
    public function checkTransform($statLabel, DOMQuery $query,
                                   $expectedElementChanges) {
        if (is_null($this->stats))
            return;
        $this->stats->checkTransform($statLabel, $query,
            $expectedElementChanges);
    }
}
