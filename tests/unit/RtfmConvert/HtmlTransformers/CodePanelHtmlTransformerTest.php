<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class CodePanelHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

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

    public function testTransformShouldTransformHtmlCodePanel() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;">
<div class="codeContent panelContent">
<pre class="code-html">        .ajaxSearch_paging {

        }
</pre>
</div>
</div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">        .ajaxSearch_paging {

        }
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
    public function testTransformShouldPreserveHtmlBetweenTwoCodePanels() {
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
     * @depends testTransformShouldPreserveHtmlBetweenTwoCodePanels
     */
    public function testTransformShouldPreserveHtmlBetweenThreeCodePanels() {
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

<p>This would output:</p>

<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java">
Hello George!
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

<p>This would output:</p>

<pre class="brush: php">
Hello George!
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
     * @depends testTransformShouldTransformCodePanelWithHeader
     */
    public function testTransformShouldPreserveHtmlBetweenTwoCodePanelsWithHeaders() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeHeader panelHeader" style="border-bottom-width: 1px;"><b>PHP Configuration Options</b></div><div class="codeContent panelContent">
<pre class="code-java">./configure --with-apxs2=/usr/local/bin/apxs --with-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
</div></div>
<p>Here's some in-between content.</p>
<div class="code panel" style="border-width: 1px;"><div class="codeHeader panelHeader" style="border-bottom-width: 1px;"><b>NGINX PHP Configuration Options</b></div><div class="codeContent panelContent">
<pre class="code-java">./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<p><b>PHP Configuration Options</b></p>
<pre class="brush: php">./configure --with-apxs2=/usr/local/bin/apxs --with-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
<p>Here's some in-between content.</p>
<p><b>NGINX PHP Configuration Options</b></p>
<pre class="brush: php">./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
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

    /**
     * @depends testTransformShouldRemoveSpans
     */
    public function testTransformShouldPreserveHtmlBetweenTwoCodePanelsWithSpans() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java">[[*pagetitle]] <span class="code-comment">// renders the pagetitle.
</span>[[*id]] <span class="code-comment">// renders the Resource's ID
</span>[[*createdby]] <span class="code-comment">// renders the ID of the user who created <span class="code-keyword">this</span> Resource</span>
</pre>
</div></div>

<p>They can also have <a href="/display/revolution20/Input+and+Output+Filters+%28Output+Modifiers%29" title="Input and Output Filters (Output Modifiers)">Output Filters</a> applied to them:</p>
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java"><span class="code-comment">// Renders a limited version of the introtext field.
</span><span class="code-comment">// If it is longer than 100 chars, adds an ...
</span>[[*introtext:ellipsis=`100`]]

<span class="code-comment">// Grabs the user who last edited the Resource's username
</span>[[*editedby:userinfo=`username`]]

<span class="code-comment">// Grabs the user who published the Resource's email
</span>[[*publishedby:userinfo=`email`]]
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">[[*pagetitle]] // renders the pagetitle.
[[*id]] // renders the Resource's ID
[[*createdby]] // renders the ID of the user who created this Resource
</pre>

<p>They can also have <a href="/display/revolution20/Input+and+Output+Filters+%28Output+Modifiers%29" title="Input and Output Filters (Output Modifiers)">Output Filters</a> applied to them:</p>
<pre class="brush: php">// Renders a limited version of the introtext field.
// If it is longer than 100 chars, adds an ...
[[*introtext:ellipsis=`100`]]

// Grabs the user who last edited the Resource's username
[[*editedby:userinfo=`username`]]

// Grabs the user who published the Resource's email
[[*publishedby:userinfo=`email`]]
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

        $this->assertTransformStat('.code.panel', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
        $this->assertTransformStat('.code.panel .codeHeader', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
        $this->assertStatsNotContain('.code.panel pre:has(span[class^="code-"])');
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
        $this->assertTransformStat('.code.panel pre:has(span[class^="code-"])',
            1, array(self::TRANSFORM => 1, self::WARNING => 0));
        $this->assertStatsNotContain(
            '.code.panel pre:has(*:not(span[class^="code-"]))');
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
        $this->assertTransformStat(
            '.code.panel pre:has(*:not(span[class^="code-"]))', 1,
            array(self::TRANSFORM => 0, self::WARNING => 1));
    }
}
