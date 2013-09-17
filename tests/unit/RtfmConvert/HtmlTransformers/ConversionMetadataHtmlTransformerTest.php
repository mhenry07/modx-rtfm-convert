<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\Analyzers\NewRtfmMetadataLoader;
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
            PageStatistics::SOURCE_PARENT_PAGE_ID_LABEL, '67890');
        $this->stats->addValueStat(
            PageStatistics::SOURCE_SPACE_KEY_LABEL, 'revolution20');
        $this->stats->addValueStat(
            PageStatistics::SOURCE_SPACE_NAME_LABEL, 'Revolution 2.0');
        $this->stats->addValueStat(
            PageStatistics::SOURCE_MODIFICATION_INFO_LABEL,
            'Added by John Doe, last modified by Jane Doe on Sep 9, 2013');
        $this->stats->addValueStat(PageStatistics::SOURCE_LABELS_LABEL,
            'svn, revolution, git, developer, advanced');
        $pageData = new PageData($html, $this->stats);

        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);

        $body = $result->top('body');
        $this->assertBodyAttr('12345', $body,
            ConversionMetadataHtmlTransformer::SOURCE_PAGE_ID_ATTR);
        $this->assertBodyAttr('67890', $body,
            ConversionMetadataHtmlTransformer::SOURCE_PARENT_PAGE_ID_ATTR);
        $this->assertBodyAttr('revolution20', $body,
            ConversionMetadataHtmlTransformer::SOURCE_SPACE_KEY_ATTR);
        $this->assertBodyAttr('Revolution 2.0', $body,
            ConversionMetadataHtmlTransformer::SOURCE_SPACE_NAME_ATTR);
        $this->assertBodyAttr(
            'Added by John Doe, last modified by Jane Doe on Sep 9, 2013',
            $body,
            ConversionMetadataHtmlTransformer::SOURCE_MODIFICATION_INFO_ATTR);
        $this->assertBodyAttr('svn, revolution, git, developer, advanced',
            $body, ConversionMetadataHtmlTransformer::SOURCE_LABELS_ATTR);
    }

    public function testTransformShouldAddHeadMetadataWhenHeadMissing() {
        $html = <<<'EOT'
<html>
<body></body>
</html>
EOT;
        $this->stats->addValueStat(
            PageStatistics::SOURCE_PAGE_TITLE_LABEL, 'Page Title');
        $pageData = new PageData($html, $this->stats);
        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);
        $head = $result->top('head');
        $this->assertEquals(1, $head->count(), 'head is missing');
        $this->assertEquals('Page Title', trim($head->find('title')->text()));
        $this->assertEquals('<meta charset="utf-8">',
            RtfmQueryPath::getHtmlString($head->firstChild()));
    }

    public function testTransformShouldAddCanonicalDestLink() {
        $destUrl = 'http://rtfm.modx.com/revolution/2.x/getting-started';
        $expected = "<link rel=\"canonical\" title=\"dest\" href=\"{$destUrl}\">";
        $html = <<<'EOT'
<html>
<head><title></title></head>
<body></body>
</html>
EOT;
        $this->stats->addValueStat(NewRtfmMetadataLoader::DEST_URL_LABEL, $destUrl);
        $pageData = new PageData($html, $this->stats);

        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);

        $lastLink = $result->top('head')->find('link')->last();
        $this->assertEquals($expected,
            RtfmQueryPath::getHtmlString($lastLink));
    }

    public function testTransformShouldAddDestMetadataToBodyAttributes() {
        $html = <<<'EOT'
<html>
<head><title></title></head>
<body></body>
</html>
EOT;
        $this->stats->addValueStat(
            NewRtfmMetadataLoader::DEST_PAGE_ID_LABEL, '777');
        $this->stats->addValueStat(
            NewRtfmMetadataLoader::DEST_MODIFICATION_INFO_LABEL,
            'Last edited by JP DeVries on Aug  8, 2013.');

        $pageData = new PageData($html, $this->stats);
        $transformer = new ConversionMetadataHtmlTransformer();
        $result = $transformer->transform($pageData);

        $body = $result->top('body');
        $this->assertBodyAttr('777', $body,
            ConversionMetadataHtmlTransformer::DEST_PAGE_ID_ATTR);
        $this->assertBodyAttr(
            'Last edited by JP DeVries on Aug  8, 2013.', $body,
            ConversionMetadataHtmlTransformer::DEST_MODIFICATION_INFO_ATTR);
    }

    protected function assertBodyAttr($expected, DOMQuery $body, $attributeName) {
        $this->assertTrue($body->hasAttr($attributeName));
        $this->assertEquals($expected, $body->attr($attributeName));
    }
}
