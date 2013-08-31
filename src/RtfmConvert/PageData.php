<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


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
     */
    public function getHtmlString() {
        if (is_string($this->html))
            return $this->html;
        $result = '';
        /** @var \QueryPath\DOMQuery $item */
        foreach ($this->html as $item)
            $result .= $this->html->document()->saveHTML($item->get(0));
        return $result;
    }

    /**
     * @return string Returns the HTML document as a string. If the internal
     * type is a \QueryPath\DOMQuery, returns the whole document as a string.
     */
    public function getHtmlDocument() {
        if (is_string($this->html))
            return $this->html;
        return $this->html->document()->saveHTML();
    }

    /**
     * @return \QueryPath\DOMQuery
     */
    public function getHtmlQuery() {
        if (is_string($this->html))
            return RtfmQueryPath::htmlqp($this->html, 'body');
        return $this->html;
    }

    /**
     * @return PageStatistics
     */
    public function getStats() {
        return $this->stats;
    }

    /**
     * @param $label
     * @param $count
     * @param bool $isTransformed
     * @param bool $warnIfFound
     * @param bool $isRequired
     */
    public function addCountStat($label, $count, $isTransformed = false,
                                 $warnIfFound = false, $isRequired = false) {
        if (is_null($this->stats))
            return;
        $this->stats->addCountStat($label, $count, $isTransformed,
            $warnIfFound, $isRequired);
    }

    /**
     * @param string $selector
     * @param bool $isTransforming
     * @param bool $warnIfFound
     * @param bool $isRequired
     */
    public function addSimpleStat($selector, $isTransforming = false,
                                  $warnIfFound = false, $isRequired = false) {
        if (is_null($this->stats))
            return;
        $qp = $this->getHtmlQuery();
        $this->stats->addCountStat($selector, $qp->find($selector)->count(),
            $isTransforming, $warnIfFound, $isRequired);
    }
}
