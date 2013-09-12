<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\RtfmQueryPath;

class ConversionMetadataHtmlTransformerTest extends HtmlTestCase {

    public function testTransformShouldSetTitle() {
        $html = <<<'EOT'
<html>
<head><title></title></head>
<body></body>
</html>
EOT;
        $this->stats->addValueStat(
            PageStatistics::SOURCE_PAGE_TITLE_LABEL, 'Page Title');
        $pageData = new PageData($html, $this->stats);
        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertEquals('Page Title', trim($result->top('title')->text()));
    }

    public function testTransformShouldAddMetaCharsetUtf8() {
        $expected = '<meta charset="utf-8">';
        $html = <<<'EOT'
<html>
<head><title></title></head>
<body></body>
</html>
EOT;
        $pageData = new PageData($html, $this->stats);
        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);
        $head = $result->top('head');
        $this->assertEquals($expected,
            RtfmQueryPath::getHtmlString($head->firstChild()));
    }

    public function testTransformShouldAddSourceLink() {
        $sourceUrl = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $expected = "<link rel=\"alternate\" title=\"source\" href=\"{$sourceUrl}\">";
        $html = <<<'EOT'
<html>
<head><title></title></head>
<body></body>
</html>
EOT;
        $this->stats->addValueStat(PageStatistics::SOURCE_URL_LABEL, $sourceUrl);
        $pageData = new PageData($html, $this->stats);
        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);
        $firstLink = $result->top('head')->find('link')->first();
        $this->assertEquals($expected,
            RtfmQueryPath::getHtmlString($firstLink));
    }

    public function testTransformShouldAddSourceMetadataToBodyAttributes() {
        $html = <<<'EOT'
<html>
<head><title></title></head>
<body></body>
</html>
EOT;
        $this->stats->addValueStat(
            PageStatistics::SOURCE_PAGE_ID_LABEL, '12345');
        $this->stats->addValueStat(
            PageStatistics::SOURCE_SPACE_KEY_LABEL, 'revolution20');
        $this->stats->addValueStat(
            PageStatistics::SOURCE_SPACE_NAME_LABEL, 'Revolution 2.0');
        $this->stats->addValueStat(
            PageStatistics::SOURCE_MODIFICATION_INFO_LABEL,
            'Added by John Doe, last modified by Jane Doe on Sep 9, 2013');
        $pageData = new PageData($html, $this->stats);
        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);
        $body = $result->top('body');

        $assertBodyAttr = function ($expected, $attributeName) use ($body) {
            $this->assertTrue($body->hasAttr($attributeName));
            $this->assertEquals($expected, $body->attr($attributeName));
        };
        $assertBodyAttr('12345',
            ConversionMetadataHtmlTransformer::SOURCE_PAGE_ID_ATTR);
        $assertBodyAttr('revolution20',
            ConversionMetadataHtmlTransformer::SOURCE_SPACE_KEY_ATTR);
        $assertBodyAttr('Revolution 2.0',
            ConversionMetadataHtmlTransformer::SOURCE_SPACE_NAME_ATTR);
        $assertBodyAttr(
            'Added by John Doe, last modified by Jane Doe on Sep 9, 2013',
            ConversionMetadataHtmlTransformer::SOURCE_MODIFICATION_INFO_ATTR);
    }
}
