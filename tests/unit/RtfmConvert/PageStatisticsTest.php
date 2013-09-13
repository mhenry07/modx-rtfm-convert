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
    public function testIncrementStatShouldAddNewStatType() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::WARNING => 1,
            PageStatistics::WARNING_MESSAGES => 'message'
        );

        $stats = new PageStatistics();
        $stats->addTransformStat('key', 1);
        $stats->incrementStat('key', PageStatistics::WARNING, 1, 'message');

        $statsArray = $stats->getStats();
        $this->assertEquals($expected, $statsArray['key']);
    }

    /**
     * @depends testAddTransformStatShouldAddExpectedStat
     */
    public function testIncrementStatShouldUpdateExistingType() {
        $expected = array(
            PageStatistics::FOUND => 3,
            PageStatistics::TRANSFORM => 3,
            PageStatistics::TRANSFORM_MESSAGES => array(
                array(PageStatistics::MESSAGE => 'message1', PageStatistics::COUNT => 1),
                array(PageStatistics::MESSAGE => 'message2', PageStatistics::COUNT => 2))
        );

        $stats = new PageStatistics();
        $stats->addTransformStat('key', 3,
            array(PageStatistics::TRANSFORM => 1,
                PageStatistics::TRANSFORM_MESSAGES => 'message1'));
        $stats->incrementStat('key', PageStatistics::TRANSFORM, 2, 'message2');

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
        $fonts = RtfmQueryPath::htmlqp($html, 'font');
        $stats = new PageStatistics();
        $stats->addQueryStat('font', $fonts, array('transformAll' => true));

        $stats->beginTransform($fonts);
        $fonts->contents()->unwrap();
        $stats->checkTransform('font', $fonts, -1);

        $this->assertEquals($expected, $stats->getStats()['font']);
    }

    /**
     * @depends testAddQueryStatShouldAddExpectedStat
     */
    public function testCheckTransformShouldWarnWhenCountNotExpected() {
        $expected = array(
            PageStatistics::FOUND => 1,
            PageStatistics::TRANSFORM => 1,
            PageStatistics::WARNING => 1,
            PageStatistics::WARNING_MESSAGES =>
            'Changed element count does not match expected. Expected: -1 Actual: -2'
        );

        $html = '<p>test <font>inner</font></p>';
        $fonts = RtfmQueryPath::htmlqp($html, 'font');
        $stats = new PageStatistics();
        $stats->addQueryStat('font', $fonts, array('transformAll' => true));

        $stats->beginTransform($fonts);
        // oops, this removes too many elements
        $fonts->top('body')->find('*')->contents()->unwrap();
        $stats->checkTransform('font', $fonts, -1);

        $this->assertEquals($expected, $stats->getStats()['font']);
    }

    public function testGetStatShouldReturnExpectedStat() {
        $expected = array(PageStatistics::FOUND => 1);

        $stats = new PageStatistics();
        $stats->addTransformStat('test', 1);

        $this->assertEquals($expected, $stats->getStat('test'));
    }

    public function testGetStatShouldReturnNullIfStatNotExist() {
        $stats = new PageStatistics();

        $this->assertNull($stats->getStat('test'));
    }

    public function testGetStatTypeShouldReturnExpectedValueForType() {
        $stats = new PageStatistics();
        $stats->addTransformStat('test', 2,
            array(PageStatistics::TRANSFORM => 1));

        $this->assertEquals(1,
            $stats->getStat('test', PageStatistics::TRANSFORM));
    }

    public function testGetStatTypeShouldReturnNullIfTypeNotExist() {
        $stats = new PageStatistics();
        $stats->addTransformStat('test', 2,
            array(PageStatistics::TRANSFORM => 1));

        $this->assertNull($stats->getStat('test', PageStatistics::WARNING));
    }
}
