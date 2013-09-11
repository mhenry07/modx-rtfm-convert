<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;

class ConfluenceAsideHtmlTransformerTest extends HtmlTestCase {
    protected $panelMacroFormat = <<<EOT
<div class='panelMacro'><table class='%sMacro'><colgroup><col width='24'><col></colgroup><tr><td valign='top'><img src="/images/icons/emoticons/%s" width="16" height="16" align="absmiddle" alt="" border="0"></td><td>%s</td></tr></table></div>
EOT;

    public function testTransformShouldTransformInfoAside() {
        $content = <<<'EOT'
To add a TV to a page, you have to think back to its template (these are <em>Template</em> variables, remember?).  Make sure you've defined the TV and attached it to the template that you're using.  See the page on <a href="/display/revolution20/Creating+a+Template+Variable" title="Creating a Template Variable">Creating a Template Variable</a>.
EOT;

        $input = sprintf($this->panelMacroFormat, 'info', 'information.gif', $content);

        $expected = <<<EOT
<div class="info">
{$content}
</div>
EOT;

        $pageData = new PageData($input);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    /**
     * @dataProvider asideTypeProvider
     */
    public function testTransformShouldTransformAsideType($type, $icon) {
        $content = 'Content';
        $input = sprintf($this->panelMacroFormat, $type, $icon, $content);

        $expected = <<<EOT
<div class="{$type}">
{$content}
</div>
EOT;

        $pageData = new PageData($input);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    // Note: oldrtfm does not seem to use danger
    public function asideTypeProvider() {
        return array(
            array('danger', 'warning.gif'),
            array('info', 'information.gif'),
            array('note', 'warning.gif'),
            array('tip', 'check.gif'),
            array('warning', 'forbidden.gif')
        );
    }

    /**
     * @depends testTransformShouldTransformInfoAside
     */
    public function testTransformShouldGenerateExpectedStat() {
        $content = 'Content';
        $input = sprintf($this->panelMacroFormat, 'info', 'information.gif', $content);

        $pageData = new PageData($input, $this->stats);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $transformer->transform($pageData);
        $this->assertTransformStat('asides', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testTransformShouldPreserveInnerParagraphs() {
        $content = <<<'EOT'
First paragraph.

<p>Second paragraph</p>
EOT;

        $input = sprintf($this->panelMacroFormat, 'info', 'information.gif', $content);

        $expected = <<<EOT
<div class="info">
{$content}
</div>
EOT;

        $pageData = new PageData($input);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldRemoveImg() {
        $content = <<<'EOT'
First paragraph.

<p>Second paragraph</p>
EOT;

        $input = sprintf($this->panelMacroFormat, 'info', 'information.gif', $content);

        $pageData = new PageData($input);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertSelectCount('img', 0, $result, '', true);
    }
}
