<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


// TODO: test utf-8 & entities
class OldRtfmContentExtractorTest extends \PHPUnit_Framework_TestCase {
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
        $wikiContent = $extractor->extract($source);
        $this->assertEquals($expected, trim($wikiContent));
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
        $wikiContent = $extractor->extract($source);
        $this->assertHtmlStringEquals($expected, $wikiContent);
    }

    public function testExtractShouldRemoveWikiContentComment() {
        $expected = 'content';
        $comment = '<!-- wiki content -->';
        $source = $this->formatTestData("{$comment}\n{$expected}");

        $extractor = new OldRtfmContentExtractor();
        $wikiContent = $extractor->extract($source);
        $this->assertEquals($expected, trim($wikiContent));
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
        $wikiContent = $extractor->extract($source);
        $this->assertEquals($expected, trim($wikiContent));
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
        $wikiContent = $extractor->extract($source);
        $this->assertEquals($expected, trim($wikiContent));
    }

    // helper methods
    protected function formatTestData($contentHtml) {
        return $this->preFormat(
            sprintf(self::WIKI_CONTENT_FORMAT, $contentHtml));
    }

    protected function preFormat($str) {
        return str_replace("\r\n", "\n", $str);
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

    protected function assertHtmlStringEquals($expectedHtml, $actualHtml, $message = '') {
        $expectedElement = htmlqp($expectedHtml, 'body')->get(0);
        $actualElement = htmlqp($actualHtml, 'body')->get(0);

        $expectedHtmlTrimmed = trim($expectedHtml);
        $actualHtmlTrimmed = trim($actualHtml);
        $formattedMessage = <<<EOT
{$message}
Expected HTML:
{$expectedHtmlTrimmed}

Actual HTML:
{$actualHtmlTrimmed}

EOT;

        $this->assertEqualXMLStructure($expectedElement, $actualElement, true,
            $formattedMessage);
    }
}
