<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class PreformattedPanelHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

    public function testTransformShouldKeepNonCodeContent() {
        $html = '<h2>Title</h2><p>Text</p>';
        $pageData = new PageData($html, $this->stats);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($html, $result);
    }

    // see http://oldrtfm.modx.com/display/ADDON/Ditto+Extenders
    public function testTransformShouldKeepEmptyContent() {
        $html = '';
        $pageData = new PageData($html, $this->stats);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($html, $result);
    }

    public function testTransformShouldTransformSimplePreformattedPanel() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedContent panelContent">
<pre>Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<pre>
Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    /**
     * @depends testTransformShouldTransformSimplePreformattedPanel
     */
    public function testTransformShouldPreserveHtmlBetweenTwoPreformattedPanels() {
        $sourceHtml = <<<'EOT'
<table class="confluenceTable"><tbody>
<tr>
<td class="confluenceTd"> <div class="preformatted panel" style="border-width: 1px;"><div class="preformattedContent panelContent">
<pre>$_SESSION["mgrValidated"]</pre>
</div></div> </td>
<td class="confluenceTd"> modX-&gt;user-&gt;isAuthenticated('mgr') </td>
</tr>
<tr>
<td class="confluenceTd"> <div class="preformatted panel" style="border-width: 1px;"><div class="preformattedContent panelContent">
<pre>$_SESSION["webValidated"]</pre>
</div></div> </td>
<td class="confluenceTd"> modX-&gt;user-&gt;isAuthenticated('web') </td>
</tr>
</tbody></table>
EOT;

        $expectedHtml = <<<'EOT'
<table class="confluenceTable"><tbody>
<tr>
<td class="confluenceTd">
<pre>$_SESSION["mgrValidated"]</pre>
</td>
<td class="confluenceTd"> modX-&gt;user-&gt;isAuthenticated('mgr') </td>
</tr>
<tr>
<td class="confluenceTd">
<pre>$_SESSION["webValidated"]</pre>
</td>
<td class="confluenceTd"> modX-&gt;user-&gt;isAuthenticated('web') </td>
</tr>
</tbody></table>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    /**
     * @depends testTransformShouldTransformSimplePreformattedPanel
     */
    public function testTransformShouldPreservePreWhiteSpace() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedContent panelContent">
<pre>CREATE TABLE `modx_discuss_sphinx_delta` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
)
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<pre>CREATE TABLE `modx_discuss_sphinx_delta` (
  `counter_id` int(11) NOT NULL,
  `max_doc_id` int(11) NOT NULL,
  PRIMARY KEY (`counter_id`)
)
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result, true);

        $htmlResult = $result->document()->saveHTML(
            $result->top()->find('pre')->get(0));
        $this->assertEquals($expectedHtml, $htmlResult);
    }

    /**
     * @depends testTransformShouldTransformSimplePreformattedPanel
     */
    public function testTransformShouldTransformPreformattedPanelWithHeader() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedHeader panelHeader" style="border-bottom-width: 1px;"><b>Header</b><b>2</b></div><div class="preformattedContent panelContent">
<pre>Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<figure class="code">
<figcaption><b>Header</b><b>2</b></figcaption>
<pre>
Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</figure>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    /**
     * @depends testTransformShouldTransformPreformattedPanelWithHeader
     */
    public function testTransformShouldPreserveHtmlBetweenTwoPreformattedPanelsWithHeaders() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedHeader panelHeader" style="border-bottom-width: 1px;"><b>Header 1</b></div><div class="preformattedContent panelContent">
<pre>Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</div></div>
<p>Here's some in-between content.</p>
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedHeader panelHeader" style="border-bottom-width: 1px;"><b>Header 2</b></div><div class="preformattedContent panelContent">
<pre>Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<figure class="code">
<figcaption><b>Header 1</b></figcaption>
<pre>
Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</figure>
<p>Here's some in-between content.</p>
<figure class="code">
<figcaption><b>Header 2</b></figcaption>
<pre>
Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</figure>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new PreformattedPanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    public function testGenerateStatisticsShouldAddExpectedStats() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedHeader panelHeader" style="border-bottom-width: 1px;"><b>Header</b></div><div class="preformattedContent panelContent">
<pre>Could not find action file at: /path/to/manager/controllers/default/welcome.php
</pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new PreformattedPanelHtmlTransformer();
        $transformer->transform($pageData);

        $this->assertTransformStat('.preformatted.panel', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0, self::ERROR => 0));
        $this->assertTransformStat('.preformatted.panel .preformattedHeader', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0, self::ERROR => 0));
        $this->assertStatsNotContain('.preformatted.panel pre:has(*)');
    }

    public function testGenerateStatisticsShouldAddPreWarning() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedContent panelContent">
<pre class="unexpected">Could not find action file at: /path/to/manager/controllers/default/welcome.php</pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new PreformattedPanelHtmlTransformer();
        $transformer->transform($pageData);

        $this->assertTransformStat('.preformatted.panel', 1,
            array(self::TRANSFORM => 1, self::WARNING => 1));
    }

    /**
     * @depends testGenerateStatisticsShouldAddExpectedStats
     */
    public function testGenerateStatisticsShouldAddPreChildrenWarning() {
        $sourceHtml = <<<'EOT'
<div class="preformatted panel" style="border-width: 1px;"><div class="preformattedContent panelContent">
<pre>Could not find action file at: <span>/path/to/manager/controllers/default/welcome.php</span></pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new PreformattedPanelHtmlTransformer();
        $transformer->transform($pageData);
        $this->assertTransformStat('.preformatted.panel pre:has(*)', 1,
            array(self::TRANSFORM => 0, self::WARNING => 1));
    }
}
