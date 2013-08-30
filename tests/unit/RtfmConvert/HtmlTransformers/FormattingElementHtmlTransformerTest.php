<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageStatistics;

class FormattingElementHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {
    public function setUp() {
        $this->stats = new PageStatistics();
    }

    public function testTransformShouldStripFontTag() {
        $text = 'If you are running your MySQL server with networking disabled, you can specify the socket name like this: ";unix_socket=MySQL".';
        $html = "<p><font color=\"#333333\">{$text}</font></p>";
        $expected = "<p>{$text}</p>";

        $transformer = new FormattingElementHtmlTransformer($html, $this->stats);
        $result = $transformer->transform();
        $this->assertHtmlEquals($expected, $result);

        $this->assertStat('font', 1, true, false);
    }

    public function testTransformShouldConverBoldItalicsTeletypeTags() {
        $html = "<p><b>strong <span>*</span></b>, <i>emphasis</i> &amp; <tt>code</tt></p>";
        $expected = "<p><strong>strong <span>*</span></strong>, <em>emphasis</em> &amp; <code>code</code></p>";

        $transformer = new FormattingElementHtmlTransformer($html, $this->stats);
        $result = $transformer->transform();
        $this->assertHtmlEquals($expected, $result);

        $this->assertStat('b', 1, true, false);
        $this->assertStat('i', 1, true, false);
        $this->assertStat('tt', 1, true, true);
    }

    public function testTransformShouldGenerateStatsForHrDelIns() {
        $html = "<hr /><p><del>del</del><ins>ins</ins></p>";

        $transformer = new FormattingElementHtmlTransformer($html, $this->stats);
        $transformer->transform();

        $this->assertStat('hr', 1, false, false);
        $this->assertStat('del', 1, false, true);
        $this->assertStat('ins', 1, false, true);
    }
}
