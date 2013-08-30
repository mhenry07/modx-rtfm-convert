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

        $qp = htmlqp('<html><head><title>Test</title></head><body></body></html>');
        $pageData = new PageData($qp);
        $this->assertEquals($expected, $pageData->getHtmlString());
    }

    public function testGetHtmlQueryShouldReturnGivenDomQuery() {
        $qp = htmlqp('<html><head><title>Test</title></head><body></body></html>');
        $pageData = new PageData($qp);
        $this->assertEquals($qp, $pageData->getHtmlQuery());
    }

    public function testGetHtmlQueryShouldReturnExpectedDomQueryGivenHtmlString() {
        $html = '<html><head><title>Test</title></head><body></body></html>';
        $qp = htmlqp($html);
        $pageData = new PageData($html);
        $this->assertHtmlEquals($qp, $pageData->getHtmlQuery());
    }

    public function testGetHtmlQueryShouldReturnExpectedTagGivenHtmlString() {
        $html = '<p>test</p>';
        $pageData = new PageData($html);
        $qp = $pageData->getHtmlQuery();
        $this->assertEquals($qp->tag(), 'body');
    }
}
