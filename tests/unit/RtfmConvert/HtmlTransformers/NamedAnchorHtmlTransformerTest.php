<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;

class NamedAnchorHtmlTransformerTest extends HtmlTestCase {

    public function testTransformH2ShouldConvertNamedAnchorToH2Id() {
        $input = '<h2><a name="named-anchor"></a>Heading</h2>';
        $expected = '<h2 id="named-anchor">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 1, true, false);
    }

    public function testTransformH3ShouldConvertNamedAnchorToH3Id() {
        $input = '<h3><a name="named-anchor"></a>Heading</h3>';
        $expected = '<h3 id="named-anchor">Heading</h3>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 1, true, false);
    }

    public function testTransformShouldConvertNamedAnchorWithId() {
        $input = '<h2><a name="named-anchor" id="named-anchor"></a>Heading</h2>';
        $expected = '<h2 id="named-anchor">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 1, true, false);
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
        $this->assertStat('named anchors: headings', 1, true, false);
    }

    public function testTransformShouldPreserveNamedAnchorWithContent() {
        $input = '<h2><a name="named-anchor">sublink</a> Heading</h2>';
        $expected = '<h2><a id="named-anchor">sublink</a> Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 1, true, false);
    }

    public function testTransformShouldPreserveNamedAnchorWhenH2HasDifferentId() {
        $input = '<h2 id="clashing-id"><a name="named-anchor"></a>Heading</h2>';
        $expected = '<h2 id="clashing-id"><a id="named-anchor"></a>Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 0, false, false);
        $this->assertStat('named anchors: heading exceptions', 1, false, true);
    }

    public function testTransformShouldPreserveNamedAnchorWithDifferentAnchorId() {
        $input = '<h2><a id="clashing-id" name="named-anchor"></a>Heading</h2>';
        $expected = $input;
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 0, false, false);
        $this->assertStat('named anchors: heading exceptions', 1, false, true);
    }

    public function testTransformShouldPreservePercentAndDot() {
        $input = '<h2><a name="YAMSSetup%28de%29-Erstellungeinerneuenbzw.ErweiterungeinereinsprachigenWebsite"></a>Heading</h2>';
        $expected = '<h2 id="YAMSSetup%28de%29-Erstellungeinerneuenbzw.ErweiterungeinereinsprachigenWebsite">Heading</h2>';
        $pageData = new PageData($input, $this->stats);
        $transformer = new NamedAnchorHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('named anchors: headings', 1, true, false);
    }
}