<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class MixedNestedListAnalyzerTest extends \PHPUnit_Framework_TestCase {

    public function testProcessShouldNotAddStatForNormalList() {
        $html = '<ul><li>item</li></ul>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new MixedNestedListAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayNotHasKey('lists: mixed nested', $stats->getStats());
    }

    public function testProcessShouldNotAddStatForUlInUl() {
        $html = '<ul><ul><li>item</li></ul></ul>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new MixedNestedListAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayNotHasKey('lists: mixed nested', $stats->getStats());
    }

    public function testProcessShouldAddErrorForUlInOl() {
        $html = '<ol><ul><li>item</li></ul></ol>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new MixedNestedListAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayHasKey('lists: mixed nested', $stats->getStats());
        $this->assertEquals(1,
            $stats->getStat('lists: mixed nested', PageStatistics::FOUND));
        $this->assertEquals(1,
            $stats->getStat('lists: mixed nested', PageStatistics::ERROR));
    }

    public function testProcessShouldAddErrorForOlInUl() {
        $html = '<ul><ol><li>item</li></ol></ul>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new MixedNestedListAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayHasKey('lists: mixed nested', $stats->getStats());
        $this->assertEquals(1,
            $stats->getStat('lists: mixed nested', PageStatistics::FOUND));
        $this->assertEquals(1,
            $stats->getStat('lists: mixed nested', PageStatistics::ERROR));
    }

    public function testProcessShouldNotAddStatForUlInLiInOl() {
        $html = '<ol><li><ul><li>item</li></ul></li></ol>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new MixedNestedListAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayNotHasKey('lists: mixed nested', $stats->getStats());
    }

    public function testProcessShouldNotAddStatForOlInLiInUl() {
        $html = '<ul><li><ol><li>item</li></ol></li></ul>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new MixedNestedListAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayNotHasKey('lists: mixed nested', $stats->getStats());
    }
}
