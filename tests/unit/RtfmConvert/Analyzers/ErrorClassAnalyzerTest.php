<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class ErrorClassAnalyzerTest extends \PHPUnit_Framework_TestCase {

    public function testProcessShouldNotAddStatForNonError() {
        $html = '<p>text</p>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new ErrorClassAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayNotHasKey('.error', $stats->getStats());
    }

    public function testProcessErrorDivShouldAddExpectedErrorClassStat() {
        $html = '<div class="error">Error</div>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new ErrorClassAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayHasKey('.error', $stats->getStats());
        $this->assertEquals(1, $stats->getStat('.error', PageStatistics::FOUND));
        $this->assertEquals(1, $stats->getStat('.error', PageStatistics::WARNING));
    }

    public function testProcessErrorSpanShouldAddExpectedErrorClassStat() {
        $html = '<span class="error">Error</span>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new ErrorClassAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertEquals($pageData, $result);
        $this->assertArrayHasKey('.error', $stats->getStats());
        $this->assertEquals(1, $stats->getStat('.error', PageStatistics::FOUND));
        $this->assertEquals(1, $stats->getStat('.error', PageStatistics::WARNING));
    }

    public function testProcessErrorDivAndSpanShouldAddExpectedErrorClassStat() {
        $html = '<div class="error">div</div><span class="error">span</span>';
        $stats = new PageStatistics();
        $pageData = new PageData($html, $stats);
        $analyzer = new ErrorClassAnalyzer();
        $result = $analyzer->process($pageData);

        $this->assertArrayHasKey('.error', $stats->getStats());
        $this->assertEquals(2, $stats->getStat('.error', PageStatistics::FOUND));
        $this->assertEquals(2, $stats->getStat('.error', PageStatistics::WARNING));
    }
}
