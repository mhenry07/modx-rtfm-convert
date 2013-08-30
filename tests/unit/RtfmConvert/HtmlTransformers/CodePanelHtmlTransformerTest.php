<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class CodePanelHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {
    public function setUp() {
        $this->stats = new PageStatistics();
    }

    public function testTransformShouldKeepNonCodeContent() {
        $html = '<h2>Title</h2><p>Text</p>';
        $pageData = new PageData($html);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($html, $result);
    }

    public function testTransformShouldTransformSimpleCodePanel() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;">
<div class="codeContent panelContent">
<pre class="code-java">
[[!getResources? &amp;parents=`123` &amp;limit=`5`]]
</pre>
</div>
</div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">
[[!getResources? &amp;parents=`123` &amp;limit=`5`]]
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    /**
     * @depends testTransformShouldTransformSimpleCodePanel
     */
    public function testTransformShouldPreserveHtmlBetweenCodePanels() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java">
Hello [[+name]]!
</pre>
</div></div>
<p>You'll note the new placeholder syntax. So, we'll definitely want to parse that Chunk's property. In Evolution, you would need to do this with a Snippet; no longer. You can simply pass a property for the Chunk:</p>
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java">
[[$Hello?name=`George`]]
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">
Hello [[+name]]!
</pre>
<p>You'll note the new placeholder syntax. So, we'll definitely want to parse that Chunk's property. In Evolution, you would need to do this with a Snippet; no longer. You can simply pass a property for the Chunk:</p>
<pre class="brush: php">
[[$Hello?name=`George`]]
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    /**
     * @depends testTransformShouldTransformSimpleCodePanel
     */
    public function testTransformShouldPreservePreWhiteSpace() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;">
<div class="codeContent panelContent">
<pre class="code-java">
[[!getResources? &amp;parents=`123` &amp;limit=`5`]]

[[!getResources?
  &amp;parents=`123`
  &amp;limit=`5`
]]
</pre>
</div>
</div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">
[[!getResources? &amp;parents=`123` &amp;limit=`5`]]

[[!getResources?
  &amp;parents=`123`
  &amp;limit=`5`
]]
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result, true);

        $htmlResult = $result->document()->saveHTML(
            $result->top()->find('pre')->get(0));
        $this->assertEquals($expectedHtml, $htmlResult);
    }

    /**
     * @depends testTransformShouldTransformSimpleCodePanel
     */
    public function testTransformShouldTransformCodePanelWithHeader() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeHeader panelHeader" style="border-bottom-width: 1px;"><b>NGINX PHP Configuration Options</b><b>2</b></div><div class="codeContent panelContent">
<pre class="code-java">./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<p><b>NGINX PHP Configuration Options</b><b>2</b></p>
<pre class="brush: php">
./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    /**
     * @depends testTransformShouldTransformSimpleCodePanel
     */
    public function testTransformShouldRemoveSpans() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java">[[*pagetitle]] <span class="code-comment">// renders the pagetitle.
</span>[[*id]] <span class="code-comment">// renders the Resource's ID
</span>[[*createdby]] <span class="code-comment">// renders the ID of the user who created <span class="code-keyword">this</span> Resource</span>
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">[[*pagetitle]] // renders the pagetitle.
[[*id]] // renders the Resource's ID
[[*createdby]] // renders the ID of the user who created this Resource
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    public function testGenerateStatisticsShouldAddExpectedStats() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeHeader panelHeader" style="border-bottom-width: 1px;"><b>NGINX PHP Configuration Options</b></div><div class="codeContent panelContent">
<pre class="code-java">./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new CodePanelHtmlTransformer();
        $transformer->transform($pageData);

        $this->assertStat('.code.panel', 1, true);
        $this->assertStat('.code.panel .codeHeader', 1, true);
        $this->assertStat('.code.panel pre:has(span[class^="code-"])', 0, false);
    }

    /**
     * @depends testGenerateStatisticsShouldAddExpectedStats
     */
    public function testGenerateStatisticsShouldAddExpectedPreSpanStats() {
        $sourceHtml = <<<'EOT'
<div class="code panel"><div class="codeContent panelContent">
<pre class="code-java">[[*createdby]] <span class="code-comment">// renders the ID of the user who created <span class="code-keyword">this</span> Resource</span></pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new CodePanelHtmlTransformer();
        $transformer->transform($pageData);
        $this->assertStat('.code.panel pre:has(span[class^="code-"])', 1, true);
    }

    /**
     * @depends testGenerateStatisticsShouldAddExpectedStats
     */
    public function testGenerateStatisticsShouldAddExpectedPreNotSpanStats() {
        $sourceHtml = <<<'EOT'
<div class="code panel"><div class="codeContent panelContent">
<pre class="code-java">[[*createdby]] <i>// renders the ID of the user who created this Resource</i></pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new CodePanelHtmlTransformer();
        $transformer->transform($pageData);
        $this->assertStat('.code.panel pre:has(:not(span[class^="code-"]))', 1, false, true);
    }
}
