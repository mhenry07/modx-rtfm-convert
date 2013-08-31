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

    public function testGetHtmlStringShouldReturnExpectedStringGivenDomQueryWithSelector() {
        $content = '<p>test</p>';
        $expected = "<body>{$content}</body>";

        $qp = htmlqp($content, 'body');
        $pageData = new PageData($qp);
        $this->assertEquals($expected, $pageData->getHtmlString());
    }

    public function testGetHtmlDocumentShouldReturnExpectedStringGivenDomQueryWithSelector() {
        $content = '<p>test</p>';
        $expected = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html><body>{$content}</body></html>

EOT;

        $qp = htmlqp($content, 'body');
        $pageData = new PageData($qp);
        $this->assertEquals($expected, $pageData->getHtmlDocument());
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
