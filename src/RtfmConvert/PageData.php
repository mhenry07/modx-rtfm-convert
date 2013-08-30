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
     * @todo allow getting full document (inc. doctype)
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
     * @return \QueryPath\DOMQuery
     */
    public function getHtmlQuery() {
        if (is_string($this->html))
            return htmlqp($this->html, 'body');
        return $this->html;
    }

    /**
     * @return PageStatistics
     */
    public function getStats() {
        return $this->stats;
    }
}
