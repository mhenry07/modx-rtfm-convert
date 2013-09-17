<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\RtfmQueryPath;

class CodePanelHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

    public function testTransformShouldKeepNonCodeContent() {
        $html = '<h2>Title</h2><p>Text</p>';
        $pageData = new PageData($html, $this->stats);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($html, $result);
    }

    // see http://oldrtfm.modx.com/display/ADDON/Ditto+Extenders
    public function testTransformShouldKeepEmptyContent() {
        $html = '';
        $pageData = new PageData($html, $this->stats);
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
     * @dataProvider syntaxLanguageProvider
     */
    public function testTransformShouldTransformCodePanelLanguage($from, $to) {
        $sourceHtml = <<<EOT
<div class="code panel" style="border-width: 1px;">
<div class="codeContent panelContent">
<pre class="code-{$from}">        .ajaxSearch_paging {

        }
</pre>
</div>
</div>
EOT;

        $expectedHtml = <<<EOT
<pre class="brush: {$to}">        .ajaxSearch_paging {

        }
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }

    public function syntaxLanguageProvider() {
        return array(
            array('html', 'html'),
            array('java', 'php'),
            array('javascript', 'javascript'),
            array('php', 'php'),
            array('sql', 'sql')
        );
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

    public function testGenerateStatisticsShouldAddPreWarning() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-unexpected">./configure --with-mysql --with-pdo-mysql --prefix=/usr/local --with-pdo-mysql --with-zlib</pre>
</div></div>
EOT;

        $pageData = new PageData($sourceHtml, $this->stats);
        $transformer = new CodePanelHtmlTransformer();
        $transformer->transform($pageData);

        $this->assertTransformStat('.code.panel', 1,
            array(self::TRANSFORM => 1, self::WARNING => 1));
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
     * Note: this was previously having issues when PageData contained an
     * instance of \QueryPath\DOMQuery.
     * @depends testGenerateStatisticsShouldAddExpectedStats
     */
    public function testGenerateStatisticsShouldNotAddPreNotSpanStats() {
        $sourceHtml = <<<'EOT'
<html>
<head><title>Test</title></head>
<body>
<p>content</p>
<div class="code panel"><div class="codeContent panelContent">
<pre class="code-java">[[*createdby]] <span class="code-comment">// renders the ID of the user who created this Resource</span></pre>
</div></div>
</body>
</html>
EOT;

        $qp = RtfmQueryPath::htmlqp($sourceHtml);
        $pageData = new PageData($qp, $this->stats);
        $transformer = new CodePanelHtmlTransformer();
        $transformer->transform($pageData);
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

    /**
     * htmlqp() chokes on the following (note the space is a utf-8 nbsp):
     * <pre> &lt;meta http-equiv content="charset=utf-8"&gt;</pre>
     * It would output: <pre>&lt;&gt;</pre>
     * see http://oldrtfm.modx.com/pages/viewpage.action?pageId=18678051
     * see see https://github.com/technosophos/querypath/issues/94
     *
     * note there are utf-8 non-breaking spaces in $sourceHtml
     */
    public function testTransformShouldHandlePreWithMetaCharsetAndNbsps() {
        $sourceHtml = <<<'EOT'
<div class="code panel" style="border-width: 1px;"><div class="codeContent panelContent">
<pre class="code-java">&lt;!DOCTYPE html PUBLIC <span class="code-quote">"-<span class="code-comment">//W3C//DTD XHTML 1.1//EN"</span>  
</span><span class="code-quote">"http:<span class="code-comment">//www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"</span>&gt;
</span>&lt;html xmlns=<span class="code-quote">"http:<span class="code-comment">//www.w3.org/1999/xhtml"</span> xml:lang=<span class="code-quote">"en"</span>&gt;
</span>&lt;head&gt;
    &lt;title&gt;My First Revolutionary Page&lt;/title&gt;
    &lt;meta http-equiv=<span class="code-quote">"Content-Type"</span> content=<span class="code-quote">"text/html; charset=utf-8"</span> /&gt;
    &lt;style type=<span class="code-quote">"text/css"</span> media=<span class="code-quote">"screen"</span>&gt;
        #content{width:80%;margin:auto;border:5px groove #a484ce;}
        #content h1{color:#a484ce;padding:10px 20px;text-align:center;}
        #content p{padding:20px;text-align:center;}
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div id=<span class="code-quote">"content"</span>&gt;
        [[*content]]
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;
</pre>
</div></div>
EOT;

        $expectedHtml = <<<'EOT'
<pre class="brush: php">
&lt;!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"  
"http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"&gt;
&lt;html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"&gt;
&lt;head&gt;
    &lt;title&gt;My First Revolutionary Page&lt;/title&gt;
    &lt;meta http-equiv="Content-Type" content="text/html; charset=utf-8" /&gt;
    &lt;style type="text/css" media="screen"&gt;
        #content{width:80%;margin:auto;border:5px groove #a484ce;}
        #content h1{color:#a484ce;padding:10px 20px;text-align:center;}
        #content p{padding:20px;text-align:center;}
    &lt;/style&gt;
&lt;/head&gt;
&lt;body&gt;
    &lt;div id="content"&gt;
        [[*content]]
    &lt;/div&gt;
&lt;/body&gt;
&lt;/html&gt;
</pre>
EOT;

        $pageData = new PageData($sourceHtml);
        $transformer = new CodePanelHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expectedHtml, $result);
    }
}
