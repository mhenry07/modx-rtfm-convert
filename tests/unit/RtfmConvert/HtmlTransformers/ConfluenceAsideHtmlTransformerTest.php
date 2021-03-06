<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

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

    // see http://oldrtfm.modx.com/display/revolution20/Automated+Server-Side+Image+Editing
    public function testTransformShouldTransformAsideWithSingleTableCell() {
        $content = <<<'EOT'
<b>Performance Hit</b><br />If you are on a shared server, remember excessive image processing can affect other users. &nbsp;Your host may contact you and/or suspend your account if it causes problems.

<p>Reducing the picture size, even if not to the exact dimensions, will reduce the resource usage and processing time.</p>
EOT;
        $input = <<<EOT
<div class='panelMacro'><table class='noteMacro'><tr><td>{$content}</td></tr></table></div>
EOT;

        $expected = <<<EOT
<div class="note">
{$content}
</div>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('asides', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
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

    // see http://oldrtfm.modx.com/display/ADDON/renderResources
    public function testTransformShouldPreserveTableInAsideContent() {
        $content = <<<'EOT'
<b>Available filter operators</b><br /><br class="atl-forced-newline" />
There are a number of comparison operators for use when creating filter conditions. In addition, when using many of these operators, numeric comparison values are automatically CAST TV values to numeric before comparison. Here is a list of the valid operators: <br class="atl-forced-newline" />
&#124;&#124; Filter Operator &#124;&#124; SQL Operator &#124;&#124; CASTs numerics &#124;&#124; Notes &#124;&#124;| &lt;=&gt; | &lt;=&gt; | Yes | <em>NULL safe equals</em> |
<div class='table-wrap'>
<table class='confluenceTable'><tbody>
<tr>
<td class='confluenceTd'> === </td>
<td class='confluenceTd'> = </td>
<td class='confluenceTd'> Yes </td>
<td class='confluenceTd'>&nbsp;</td>
</tr>
</tbody></table>
</div>
EOT;

        $expected = <<<EOT
<div class="info">
{$content}
</div>
EOT;

        $input = sprintf($this->panelMacroFormat, 'info', 'information.gif', $content);

        $pageData = new PageData($input, $this->stats);
        $transformer = new ConfluenceAsideHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('asides', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0,
                PageStatistics::ERROR => 0));
    }
}
