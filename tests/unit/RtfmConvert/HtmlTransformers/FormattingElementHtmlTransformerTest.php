<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class FormattingElementHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

    public function testTransformShouldStripFontTag() {
        $text = 'If you are running your MySQL server with networking disabled, you can specify the socket name like this: ";unix_socket=MySQL".';
        $html = "<p><font color=\"#333333\">{$text}</font></p>";
        $expected = "<p>{$text}</p>";

        $pageData = new PageData($html, $this->stats);
        $transformer = new FormattingElementHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);

        $this->assertTransformStat('font', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformShouldStripMultipleFontTags() {
        $html = "<p><font>text1</font></p><p><font>text2</font></p>";
        $expected = "<p>text1</p><p>text2</p>";

        $pageData = new PageData($html, $this->stats);
        $transformer = new FormattingElementHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldConvertBoldItalicsTeletypeTags() {
        $html = "<p><b>strong <span>*</span></b>, <i>emphasis</i> &amp; <tt>code</tt></p>";
        $expected = "<p><strong>strong <span>*</span></strong>, <em>emphasis</em> &amp; <code>code</code></p>";

        $pageData = new PageData($html, $this->stats);
        $transformer = new FormattingElementHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);

        $this->assertTransformStat('b', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
        $this->assertTransformStat('i', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
        $this->assertTransformStat('tt', 1,
            array(self::TRANSFORM => 1, self::WARNING => 1));
    }

    public function testTransformShouldConvertMultipleBolds() {
        $html = <<<'EOT'
<p><b>text1</b></p>
<p><b>text2</b></p>
EOT;

        $expected = <<<'EOT'
<p><strong>text1</strong></p>
<p><strong>text2</strong></p>
EOT;

        $pageData = new PageData($html, $this->stats);
        $transformer = new FormattingElementHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldGenerateStatsForHrDelIns() {
        $html = "<hr /><p><del>del</del><ins>ins</ins></p>";

        $pageData = new PageData($html, $this->stats);
        $transformer = new FormattingElementHtmlTransformer();
        $transformer->transform($pageData);

        $this->assertTransformStat('hr', 1,
            array(self::TRANSFORM => 0, self::WARNING => 0));
        $this->assertTransformStat('del', 1,
            array(self::TRANSFORM => 0, self::WARNING => 1));
        $this->assertTransformStat('ins', 1,
            array(self::TRANSFORM => 0, self::WARNING => 1));
    }
}
