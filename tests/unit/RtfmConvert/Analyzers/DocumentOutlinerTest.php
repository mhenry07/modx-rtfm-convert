<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


require_once('RtfmConvert/HtmlTestCase.php');
use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class DocumentOutlinerTest extends HtmlTestCase {

    public function testProcessGivenNoHeadingsShouldNotAddOutline() {
        $html = '<html><head><title>Title</title></head><body><p>content</p></body></html>';
        $pageData = new PageData($html, $this->stats);

        $outliner = new DocumentOutliner();
        $result = $outliner->process($pageData);

        $this->assertStatsNotContain('outline');
    }

    public function testProcessGivenH1ShouldAddExpectedOutline() {
        $expected = array('h1. heading 1');
        $html = '<html><head><title>Title</title></head><body><h1>heading 1</h1><p>content</p></body></html>';
        $pageData = new PageData($html, $this->stats);

        $outliner = new DocumentOutliner();
        $result = $outliner->process($pageData);

        $this->assertValueStat('outline', $expected);
    }

    public function testProcessGivenMultipleHeadingsShouldAddExpectedOutline() {
        $expected = array('h1. heading 1', 'h2. heading 2', 'h3. heading 3',
            'h2. heading 4');
        $html = <<<'EOT'
<html>
<head><title>Title</title></head>
<body>
<h1>heading 1</h1>
<p>content</p>
<h2>heading 2</h2>
<p>content</p>
<h3>heading 3</h3>
<p>content</p>
<h2>heading 4</h2>
<p>content</p>
</body>
</html>
EOT;

        $pageData = new PageData($html, $this->stats);

        $outliner = new DocumentOutliner();
        $result = $outliner->process($pageData);

        $this->assertValueStat('outline', $expected);
    }

    public function testProcessGivenMultipleHeadingsInDivsShouldAddExpectedOutline() {
        $expected = array('h1. heading 1', 'h2. heading 2', 'h3. heading 3',
            'h2. heading 4');
        $html = <<<'EOT'
<html>
<head><title>Title</title></head>
<body>
<div>
<h1>heading 1</h1>
<p>content</p>
</div>
<h2>heading 2</h2>
<p>content</p>
<div><div>
<h3>heading 3</h3>
<p>content</p>
</div></div>
<h2>heading 4</h2>
<p>content</p>
</body>
</html>
EOT;

        $pageData = new PageData($html, $this->stats);

        $outliner = new DocumentOutliner();
        $result = $outliner->process($pageData);

        $this->assertValueStat('outline', $expected);
    }

    public function testProcessWithPrefixShouldAddOutlineWithPrefix() {
        $expected = array('h1. heading 1');
        $html = '<html><head><title>Title</title></head><body><h1>heading 1</h1><p>content</p></body></html>';
        $pageData = new PageData($html, $this->stats);

        $outliner = new DocumentOutliner('prefix: ');
        $result = $outliner->process($pageData);

        $this->assertValueStat('prefix: outline', $expected);
    }

    public function testProcessWithCompareToPrefixShouldAddOutlineAndCompare() {
        $expected = array('h1. heading 1');
        $html = '<html><head><title>Title</title></head><body><h1>heading 1</h1><p>content</p></body></html>';
        $pageData = new PageData($html, $this->stats);
        $pageData->addValueStat('prefix1: outline',
            array('h1. heading 1', 'h2. heading 2'));

        $outliner = new DocumentOutliner('prefix2: ', 'prefix1: ');
        $result = $outliner->process($pageData);

        $this->assertValueStat('prefix2: outline', $expected,
            array(PageStatistics::ERROR => 1));
    }

    public function testProcessGivenH1ShouldCollapseWhitespace() {
        $expected = array('h1. my heading 1');
        $html = "<html><head><title>Title</title></head><body><h1> my  heading \n 1 </h1><p>content</p></body></html>";
        $pageData = new PageData($html, $this->stats);

        $outliner = new DocumentOutliner();
        $result = $outliner->process($pageData);

        $this->assertValueStat('outline', $expected);
    }

}
