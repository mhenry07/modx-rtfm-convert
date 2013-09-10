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
        $stats->addTransformStat('transform', 5,
            array(PageStatistics::TRANSFORM => 3));

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('transform', $statsArray);
        $this->assertEquals($expected, $statsArray['transform']);
    }

    /**
     * @depends testAddTransformStatShouldAddExpectedStat
     */
    public function testAddTransformStatWithTransformAllShouldAddExpectedStatWhenNotFound() {
        $expected = array(PageStatistics::FOUND => 0);

        $stats = new PageStatistics();
        $stats->addTransformStat('transformAllEmpty', 0,
            array('transformAll' => true));

        $statsArray = $stats->getStats();
        $this->assertArrayHasKey('transformAllEmpty', $statsArray);
        $this->assertEquals($expected, $statsArray['transformAllEmpty']);
    }

    /**
     * @depends testAddTransformStatShouldAddExpectedStat
     */
    public function testAddQueryStatShouldAddExpectedStat() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::TRANSFORM => 1,
            PageStatistics::WARNING => 1);

        $query = qp('<p class="warning"></p>')->find('p.warning');
        $stats = new PageStatistics();
        $stats->addQueryStat('dom', $query,
            array('transformAll' => true, 'warnIfFound' => true));

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

    /**
     * @depends testAddQueryStatShouldAddExpectedStat
     */
    public function testCheckTransformShouldPreserveStatWhenCountMatchesExpected() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::TRANSFORM => 1);

        $html = '<p>test <font>inner</font></p>';
        $qp = RtfmQueryPath::htmlqp($html);
        $stats = new PageStatistics();
        $stats->addQueryStat('font', $qp, array('transformAll' => true));

        $stats->beginTransform($qp);
        $qp->find('font')->contents()->unwrap();
        $stats->checkTransform('font', $qp, 1, -1);

        $this->assertEquals($expected, $stats->getStats()['font']);
    }

    public function testCheckTransformShouldWarnWhenCountNotExpected() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::TRANSFORM => 1,
            PageStatistics::WARNING => 1,
            PageStatistics::getMessagesLabelFor(PageStatistics::WARNING) =>
            'Changed element count does not match expected. Expected: -1 Actual: -2'
        );

        $html = '<p>test <font>inner</font></p>';
        $qp = RtfmQueryPath::htmlqp($html);
        $stats = new PageStatistics();
        $stats->addQueryStat('font', $qp, array('transformAll' => true));

        $stats->beginTransform($qp);
        // oops, this removes too many elements
        $qp->find('*')->contents()->unwrap();
        $stats->checkTransform('font', $qp, 1, -1);

        $this->assertEquals($expected, $stats->getStats()['font']);
    }
}
