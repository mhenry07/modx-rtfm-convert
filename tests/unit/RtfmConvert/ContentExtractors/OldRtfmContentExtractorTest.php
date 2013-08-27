<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


require_once('RtfmConvert/HtmlTestCase.php');

// TODO: handle incomplete content (i.e. missing /div for .wiki-content)
class OldRtfmContentExtractorTest extends \RtfmConvert\HtmlTestCase {
    const WIKI_CONTENT_FORMAT = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
<title>Test</title>
</head>
<body>
    <div class="wiki-content">
        %s
    </div>
</body>
</html>
EOT;

    public function testExtractShouldReturnSimpleWikiContent() {
        $expected = 'content';
        $source = $this->formatTestData($expected);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractShouldReturnHtmlWikiContent() {
        $expected = <<<'EOT'
<h2><a name="welcome_screen-welcomescreen"></a>welcome_screen</h2>

<p><b>Name</b>: Show Welcome Screen<br/>
<b>Type</b>: Yes/No<br/>
<b>Default</b>: No</p>
EOT;

        $source = $this->formatTestData($expected);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertHtmlEquals($expected, $extracted);
    }

    public function testExtractShouldRemoveWikiContentComment() {
        $expected = 'content';
        $comment = '<!-- wiki content -->';
        $source = $this->formatTestData("{$comment}\n{$expected}");

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractShouldRemoveStyleFromContent() {
        $expected = 'content';
        $style = <<<'EOT'
<style type='text/css'>/*<![CDATA[*/
h1 { color: red }
/*]]>*/</style>
EOT;

        $source = $this->formatTestData("{$expected}\n{$style}");

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractShouldRemoveScriptFromContent() {
        $expected = 'content';
        $script = <<<'EOT'
<script type="text/x-template" title="manage-watchers-dialog">
<div class="dialog-content">
    template
</div>
</script>
EOT;

        $source = $this->formatTestData("{$expected}\n{$script}");

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractShouldRemoveScrollbarFromContent() {
        $expected = 'content';
        $scrollbar = <<<'EOT'
<div class="Scrollbar"><table class='ScrollbarTable'><tr><td class='ScrollbarPrevIcon'><a href="/display/revolution20/Structuring+Your+Site"><img border='0' align='middle' src='/images/icons/back_16.gif' width='16' height='16'></a></td><td width='33%' class='ScrollbarPrevName'><a href="/display/revolution20/Structuring+Your+Site">Structuring Your Site</a>&nbsp;</td><td width='33%' class='ScrollbarParent'><sup><a href="/display/revolution20/Making+Sites+with+MODx"><img border='0' align='middle' src='/images/icons/up_16.gif' width='8' height='8'></a></sup><a href="/display/revolution20/Making+Sites+with+MODx">Making Sites with MODx</a></td><td width='33%' class='ScrollbarNextName'>&nbsp;<a href="/display/revolution20/Customizing+Content">Customizing Content</a></td><td class='ScrollbarNextIcon'><a href="/display/revolution20/Customizing+Content"><img border='0' align='middle' src='/images/icons/forwd_16.gif' width='16' height='16'></a></td></tr></table></div>
EOT;

        $source = $this->formatTestData("{$expected}\n{$scrollbar}");

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractMissingContentShouldThrowException() {
        $source = <<<'EOT'
<!DOCTYPE html>
<html>
<head>
<title>Test</title>
</head>
<body>
EOT;

        $extractor = new OldRtfmContentExtractor();
        $this->setExpectedException('\RtfmConvert\RtfmException');
        $extractor->extract($source);
    }

    public function testExtractShouldPreserveExpectedEntities() {
        $expected = '<p>&amp; &gt; &lt; &nbsp;</p>';
        $source = $this->formatTestData($expected);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    // should this test &apos; and &quot;?
    public function testExtractShouldConvertExpectedEntities() {
        $expected = '<p>! \' ( * + - [ ] ^ _ ~ –</p>';
        $content = '<p>&#33; &#39; &#40; &#42; &#43; &#45; &#91; &#93; &#94; &#95; &#126; &#8211;</p>';
        $source = $this->formatTestData($content);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractShouldReturnUtf8() {
        $checkmark = html_entity_decode('&#x2713;', ENT_HTML401, 'UTF-8'); // ✓
        $expected = "<p>{$checkmark}</p>";
        $content = '<p>&#x2713;</p>';
        $source = $this->formatTestData($content);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertNotEquals($expected,
            trim(iconv('UTF-8', 'ISO-8859-1//IGNORE', $extracted)));
//            trim(mb_convert_encoding($extracted, 'ISO-8859-1', 'UTF-8')));
        $this->assertEquals($expected, trim($extracted));
    }

    public function testExtractShouldNotReturnCrAsEntity() {
        $source = "<html><body><div class=\"wiki-content\"><p>\r\n</p></div></body></html>";

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertNotContains('&#13;', $extracted);
        $this->assertRegExp('#^<p>\r?\n</p>$#', trim($extracted));
    }

    public function testExtractShouldNormalizeAttributeQuotes() {
        $expected = '<p id="no-quote" class="single-quote"></p>';
        $content = "<p id=no-quote class='single-quote'></p>";
        $source = $this->formatTestData($content);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    // helper methods
    protected function formatTestData($contentHtml) {
        return sprintf(self::WIKI_CONTENT_FORMAT, $contentHtml);
    }

//    protected function tidy($html) {
//        $tidy = new \tidy();
//        $tidyConfig = array(
//            'output-xhtml' => true,
//            'show-body-only' => true,
//            'char-encoding' => 'utf8',
//            'newline' => 'LF',
//            'output-bom' => false);
//        return $tidy->repairString($html, $tidyConfig);
//    }
}
