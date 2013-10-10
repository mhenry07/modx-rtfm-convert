<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;
use RtfmConvert\RtfmQueryPath;

class NamedAnchorHtmlTransformerTest extends HtmlTestCase {

    public function testTransformH2ShouldPreserveNonHeadings() {
        $input = '<p>content</p>';
        $expected = $input;
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 0,
            array(self::TRANSFORM => 0, self::WARNING => 0));
    }

    public function testTransformH2ShouldConvertNamedAnchorToH2Id() {
        $input = '<h2><a name="named-anchor"></a>Heading</h2>';
        $expected = '<h2 id="named-anchor">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformH3ShouldConvertNamedAnchorToH3Id() {
        $input = '<h3><a name="named-anchor"></a>Heading</h3>';
        $expected = '<h3 id="named-anchor">Heading</h3>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformShouldConvertNamedAnchorWithId() {
        $input = '<h2><a name="named-anchor" id="named-anchor"></a>Heading</h2>';
        $expected = '<h2 id="named-anchor">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformShouldConvertNamedAnchorWithWhitespace() {
        $input = <<<'EOT'
<h2>
    <a name="named-anchor"></a>Heading
</h2>
EOT;
        $expected = '<h2 id="named-anchor">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformShouldPreserveNamedAnchorWithContent() {
        $input = '<h2><a name="named-anchor">sublink</a> Heading</h2>';
        $expected = '<h2><a id="named-anchor">sublink</a> Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformShouldPreserveNamedAnchorWhenH2HasDifferentId() {
        $input = '<h2 id="clashing-id"><a name="named-anchor"></a>Heading</h2>';
        $expected = '<h2 id="clashing-id"><a id="named-anchor"></a>Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 0, self::WARNING => 1));
    }

    public function testTransformShouldPreserveNamedAnchorWithDifferentAnchorId() {
        $input = '<h2><a id="clashing-id" name="named-anchor"></a>Heading</h2>';
        $expected = $input;
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 0, self::WARNING => 1));
    }

    public function testTransformShouldPreserveDot() {
        $input = '<h2><a name="YAMSSetup(de)-Erstellungeinerneuenbzw.ErweiterungeinereinsprachigenWebsite"></a>Heading</h2>';
        $expected = '<h2 id="YAMSSetup(de)-Erstellungeinerneuenbzw.ErweiterungeinereinsprachigenWebsite">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    // HTML5 seems to decode links like #Image%2B-WhatisImage%3F before navigating
    public function testTransformShouldDecodePercentEncodedCharacters() {
        $input = '<h2><a name="Image%2B-WhatisImage%3F"></a>Heading</h2>';
        $expected = '<h2 id="Image+-WhatisImage?">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    // note that the resulting id is wrapped in single quotes
    public function testTransformShouldHandleEncodedQuotes() {
        $input = '<h2><a name="double%22quote"></a>Heading</h2>';
        $expected = '<h2 id="double&quot;quote">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);

        $this->assertContains('<h2 id=\'double"quote\'>',
            RtfmQueryPath::getHtmlString($result));
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: headings', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    // e.g. http://oldrtfm.modx.com/display/ADDON/Login.Login
    public function testTransformNonHeadingNamedAnchorShouldConvertNameToId() {
        $input = '<p><a name="named-anchor"></a>text</p>';
        $expected = '<p><a id="named-anchor"></a>text</p>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('named anchors: others', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }
}
