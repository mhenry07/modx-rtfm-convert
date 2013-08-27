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

    public function addCountStat($label, $count, $isTransformed = false,
                                 $warnIfFound = false, $isRequired = false) {
        $isWarning = $count > 0 ? $warnIfFound : $isRequired;
        if ($count === 0)
            $isTransformed = false;
        $this->add($label, $count, $isTransformed, $isWarning);
    }

    public function getStats() {
        return $this->stats;
    }
}
