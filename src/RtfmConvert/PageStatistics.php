<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class PageStatistics {
    private $stats = array();

    public function add($label, $value, $isTransformed = false, $isWarning = false) {
        $item = array(
            'label' => $label,
            'value' => $value,
            'transformed' => $isTransformed,
            'warning' => $isWarning
        );
        $this->stats[$label] = $item;
    }

    public function getStats() {
        return $this->stats;
    }
}
