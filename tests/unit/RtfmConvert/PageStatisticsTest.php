<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\Statistics;

class PageStatisticsTest extends \PHPUnit_Framework_TestCase {

    public function testAddShouldAddExpectedStat() {
        $stats = new PageStatistics();
        $stats->add('label', 5);

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('label', $statsArray);
        $stat = $statsArray['label'];
        $this->assertEquals(5, $stat['value']);
    }
}
