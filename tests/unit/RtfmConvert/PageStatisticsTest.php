<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


// TODO: add more tests
class PageStatisticsTest extends \PHPUnit_Framework_TestCase {

    public function testAddValueStatShouldAddExpectedStat() {
        $expected = array(PageStatistics::VALUE => 'value');

        $stats = new PageStatistics();
        $stats->addValueStat('my label', 'value');

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('my label', $statsArray);
        $this->assertEquals($expected, $statsArray['my label']);
    }

    public function testAddTransformStatShouldAddExpectedStat() {
        $expected = array(
            PageStatistics::FOUND => 5,
            PageStatistics::TRANSFORM => 3);

        $stats = new PageStatistics();
        $stats->addTransformStat('transform', 5, 3);

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('transform', $statsArray);
        $this->assertEquals($expected, $statsArray['transform']);
    }

    public function testAddQueryStatShouldAddExpectedStat() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::TRANSFORM => 1,
            PageStatistics::WARNING => 1);

        $query = qp('<p></p>')->find('p');
        $stats = new PageStatistics();
        $stats->addQueryStat($query, 'dom', true, null, true);

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('dom', $statsArray);
        $this->assertEquals($expected, $statsArray['dom']);
    }

    /**
     * @depends testAddTransformStatShouldAddExpectedStat
     */
    public function testIncrementStatShouldUpdateExpectedStat() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::WARNING => 1,
            PageStatistics::getMessagesLabelFor(PageStatistics::WARNING) => 'message'
        );

        $stats = new PageStatistics();
        $stats->addTransformStat('key', 1);
        $stats->incrementStat('key', PageStatistics::WARNING, 1, 'message');

        $statsArray = $stats->getStats();
        $this->assertEquals($expected, $statsArray['key']);
    }

    public function testAddShouldAddExpectedStat() {
        $stats = new PageStatistics();
        $stats->add('label', 5);

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('label', $statsArray);
        $stat = $statsArray['label'];
        $this->assertEquals(5, $stat[PageStatistics::VALUE]);
    }
}
