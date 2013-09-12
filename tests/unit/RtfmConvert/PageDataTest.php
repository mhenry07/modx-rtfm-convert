<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class PageDataTest extends HtmlTestCase {

    public function testGetHtmlStringShouldReturnGivenHtmlString() {
        $html = '<html><head><title>Test</title></head><body></body></html>';
        $pageData = new PageData($html);
        $this->assertEquals($html, $pageData->getHtmlString());
    }

    public function testGetHtmlStringShouldReturnExpectedStringGivenDomQuery() {
        $expected = <<<'EOT'
<html>
<head><title>Test</title></head>
<body></body>
</html>
EOT;

        $qp = RtfmQueryPath::htmlqp('<html><head><title>Test</title></head><body></body></html>');
        $pageData = new PageData($qp);
        $this->assertContains($expected, $pageData->getHtmlString());
    }

    public function testGetHtmlStringShouldReturnExpectedStringGivenDomQueryWithSelector() {
        $content = '<p>test</p>';
        $expected = <<<EOT
<body>{$content}</body>
EOT;

        $qp = RtfmQueryPath::htmlqp($content, 'body');
        $pageData = new PageData($qp);
        $this->assertEquals($expected, $pageData->getHtmlString());
    }

    public function testGetHtmlDocumentShouldReturnExpectedStringGivenDomQueryWithSelector() {
        $content = '<p>test</p>';
        $expected = <<<EOT
<html><body>{$content}</body></html>
EOT;

        $qp = RtfmQueryPath::htmlqp($content, 'body');
        $pageData = new PageData($qp);
        $this->assertEquals($expected, $pageData->getHtmlDocument());
    }

    public function testGetHtmlQueryShouldReturnGivenDomQuery() {
        $qp = RtfmQueryPath::htmlqp('<html><head><title>Test</title></head><body></body></html>');
        $pageData = new PageData($qp);
        $this->assertEquals($qp, $pageData->getHtmlQuery());
    }

    public function testGetHtmlQueryShouldReturnExpectedDomQueryGivenHtmlString() {
        $html = '<html><head><title>Test</title></head><body></body></html>';
        $pageData = new PageData($html);
        $this->assertHtmlEquals($html, $pageData->getHtmlQuery());
    }

    public function testGetHtmlQueryShouldReturnExpectedTagGivenHtmlString() {
        $html = '<p>test</p>';
        $pageData = new PageData($html);
        $qp = $pageData->getHtmlQuery();
        $this->assertEquals($qp->tag(), 'body');
    }

    public function testGetHtmlQueryWithSelectorShouldReturnExpectedTagGivenHtmlString() {
        $html = '<p>test</p>';
        $pageData = new PageData($html);
        $qp = $pageData->getHtmlQuery('p');
        $this->assertEquals($qp->tag(), 'p');
    }

    public function testGetHtmlQueryWithSelectorShouldReturnExpectedTagGivenDomQuery() {
        $html = <<<'EOT'
<html>
<head><title>Test</title></head>
<body><div>1</div><p>test</p></body>
</html>
EOT;

        $sourceQp = RtfmQueryPath::htmlqp($html, 'div');
        $pageData = new PageData($sourceQp);
        $result = $pageData->getHtmlQuery('p');
        $this->assertEquals('p', $result->tag());
    }
}
