<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageStatistics;

require_once('RtfmConvert/HtmlTestCase.php');

class CodePanelHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

    public function testTransformShouldKeepNonCodeContent() {
        $html = '<h2>Title</h2><p>Text</p>';
        $transformer = new CodePanelHtmlTransformer($html);
        $result = $transformer->transform();
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

        $transformer = new CodePanelHtmlTransformer($sourceHtml);
        $result = $transformer->transform();
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

        $transformer = new CodePanelHtmlTransformer($sourceHtml);
        $result = $transformer->transform();
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

        $transformer = new CodePanelHtmlTransformer($sourceHtml);
        $result = $transformer->transform();
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

        $transformer = new CodePanelHtmlTransformer($sourceHtml);
        $result = $transformer->transform();
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    public function testGenerateStatisticsShouldAddExpectedStats() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeHeader panelHeader" style="border-bottom-width: 1px;"><b>NGINX PHP Configuration Options</b></div><div class="codeContent panelContent">
<pre class="code-java">./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
</div></div>
EOT;

        $transformed = true;
        $expectedStats = array(
            array('label' => '.code.panel', 'value' => 1, 'transformed' => $transformed, 'warning' => false),
            array('label' => '.code.panel .codeHeader', 'value' => 1, 'transformed' => $transformed, 'warning' => false),
            array('label' => '.code.panel pre:has(span[class^="code-"])', 'value' => 0, 'transformed' => false, 'warning' => false)
        );

        $stats = new PageStatistics();
        $transformer = new CodePanelHtmlTransformer($sourceHtml, $stats);
        $transformer->generateStatistics($transformed);
        $statsArray = $stats->getStats();
        foreach ($expectedStats as $expectedStat) {
            $key = $expectedStat['label'];
            $this->assertArrayHasKey($key, $statsArray);
            $this->assertEquals($expectedStat, $statsArray[$key]);
        }
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

        $expectedStat = array('label' => '.code.panel pre:has(span[class^="code-"])', 'value' => 1, 'transformed' => true, 'warning' => false);

        $stats = new PageStatistics();
        $transformer = new CodePanelHtmlTransformer($sourceHtml, $stats);
        $transformer->generateStatistics(true);

        $statsArray = $stats->getStats();
        $key = $expectedStat['label'];
        $this->assertArrayHasKey($key, $statsArray);
        $this->assertEquals($expectedStat, $statsArray[$key]);
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

        $expectedStat = array('label' => '.code.panel pre:has(:not(span[class^="code-"]))', 'value' => 1, 'transformed' => false, 'warning' => true);

        $stats = new PageStatistics();
        $transformer = new CodePanelHtmlTransformer($sourceHtml, $stats);
        $transformer->generateStatistics();

        $statsArray = $stats->getStats();
        $key = $expectedStat['label'];
        $this->assertArrayHasKey($key, $statsArray);
        $this->assertEquals($expectedStat, $statsArray[$key]);
    }
}
