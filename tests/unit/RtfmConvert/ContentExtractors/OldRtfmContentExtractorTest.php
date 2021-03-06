<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


require_once('RtfmConvert/HtmlTestCase.php');
use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageStatistics;

class OldRtfmContentExtractorTest extends HtmlTestCase {
    const WIKI_CONTENT_FORMAT = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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
        $expected = '<p>content</p>';
        $scrollbar = <<<'EOT'
<div class="Scrollbar"><table class='ScrollbarTable'><tr><td class='ScrollbarPrevIcon'><a href="/display/revolution20/Structuring+Your+Site"><img border='0' align='middle' src='/images/icons/back_16.gif' width='16' height='16'></a></td><td width='33%' class='ScrollbarPrevName'><a href="/display/revolution20/Structuring+Your+Site">Structuring Your Site</a>&nbsp;</td><td width='33%' class='ScrollbarParent'><sup><a href="/display/revolution20/Making+Sites+with+MODx"><img border='0' align='middle' src='/images/icons/up_16.gif' width='8' height='8'></a></sup><a href="/display/revolution20/Making+Sites+with+MODx">Making Sites with MODx</a></td><td width='33%' class='ScrollbarNextName'>&nbsp;<a href="/display/revolution20/Customizing+Content">Customizing Content</a></td><td class='ScrollbarNextIcon'><a href="/display/revolution20/Customizing+Content"><img border='0' align='middle' src='/images/icons/forwd_16.gif' width='16' height='16'></a></td></tr></table></div>
EOT;

        $source = $this->formatTestData("{$expected}\n{$scrollbar}");

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source, $this->stats);
        $this->assertEquals($expected, trim($extracted));
        $this->assertTransformStat('div.Scrollbar', 1,
            array(self::TRANSFORM => 1, self::WARNING => 0));
    }

    public function testExtractMissingContentShouldThrowException() {
        $source = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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
        $expected = '<p>&amp; &gt; &lt;</p>'; // restore &nbsp; in post-processing
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

    /**
     * The following issue was occuring when no charset was defined:
     * see http://oldrtfm.modx.com/pages/viewpage.action?pageId=13205690
     * expected: YAMS Documentación Español
     * erroneous output: YAMS DocumentaciÃ³n EspaÃ±ol
     * Addressed by \RtfmConvert\TextTransformers\CharsetDeclarationTextTransformer
     */
    public function testExtractShouldPreserveEspanolWhenCharsetDeclared() {
        $input = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Test</title>
</head>
<body>
    <div class="wiki-content">
        %s
    </div>
</body>
</html>
EOT;

        $expected = '<h1>YAMS: Documentación en Español</h1>';
        $source = sprintf($input, $expected);

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertEquals($expected, trim($extracted));
    }

    /**
     * @see testExtractShouldPreserveEspanolWhenCharsetDeclared
     */
    public function testExtractShouldAddErrorIfCharsetIsNotDeclared() {
        $input = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
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

        $source = sprintf($input, '<h1>YAMS: Documentación en Español</h1>');

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source, $this->stats);

        $this->assertTransformStat('charset declaration', 0,
            array(PageStatistics::ERROR => 1));
    }

    public function testExtractShouldNotReturnCrAsEntity() {
        $source = "<html><body><div class=\"wiki-content\"><p>\r\n</p></div></body></html>";

        $extractor = new OldRtfmContentExtractor();
        $extracted = $extractor->extract($source);
        $this->assertNotContains('&#13;', $extracted);
        $this->assertRegExp('#^<p>\r?\n</p>$#', trim($extracted));
    }

    public function testExtractShouldGetPageInfo() {
        $pageId = '18678198';
        $pageTitle = 'Getting Started';
        $parentPageId = '18678050';
        $spaceKey = 'revolution20';
        $spaceName = 'MODx Revolution 2.x';
        $source = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>{$pageTitle} - {$spaceName} - MODX Documentation</title>
    <meta id="confluence-space-key" name="confluence-space-key" content="{$spaceKey}">
</head>
<body>
<div id="main" >
    <div id="navigation" class="content-navigation view">
        <fieldset class="hidden parameters">
            <input type="hidden" id="pageId" value="{$pageId}">
        </fieldset>
    </div>

    <h1 id="title-heading" class="pagetitle">
                    <a href="/display/{$spaceKey}"><img class="logo global custom" src="/download/attachments/9109505/global.logo?version=2&modificationDate=1356105314000" alt=""></a>
		<span id="title-text">
					            <a href="/display/{$spaceKey}/Home">{$pageTitle}</a>
    				</span>
    </h1>

    <div id="content" class="page view">
<fieldset class="hidden parameters">
    <input type="hidden" title="pageTitle" value="{$pageTitle}"/>
    <input type="hidden" title="spaceKey" value="{$spaceKey}"/>
    <input type="hidden" title="spaceName" value="{$spaceName}"/>
</fieldset>

<script type="text/x-template" title="movePageErrors">
    <div id="move-errors" class="hidden warning"></div>
</script>
<script type="text/x-template" title="movePageBreadcrumb">
    <div><li><a class="{2}" title="{3}" tabindex="-1"><span>{0}</span></a></li></div>
</script>

        <fieldset class="hidden parameters">
            <input type="hidden" title="parentPageId" value="{$parentPageId}">
        </fieldset>

<div class="page-metadata">
        <ul>
                            <li class="page-metadata-item noprint">
    <a  id="content-metadata-page-restrictions" href="#"  class="page-metadata-icon page-restrictions"   title="Page restrictions apply. Click the lock icon to view or edit the restriction.">
                   <span>Page restrictions apply</span></a>        </li>
                        <li class="page-metadata-modification-info">
                                    Added by <a href="/display/~splittingred"
                          class="url fn"
                   >Shaun McCormick</a>, last edited by <a href="/display/~smashingred"
                          class="url fn"
                   >Jay Gilmore</a> on Sep 28, 2012
                                                                <span class="noprint">&nbsp;(<a id="view-change-link" href="/pages/diffpages.action?pageId=18678050&originalId=41484505">view change</a>)</span>
                                                </li>
                            <li class="show-hide-comment-link">
                    <a id="show-version-comment" class="inline-control-link" href="#">show comment</a>
                    <a id="hide-version-comment" class="inline-control-link" style="display:none;" href="#">hide comment</a>
                </li>
                    </ul>
          <div id="version-comment" class="noteMacro" style="display: none;">
      <strong>Comment:</strong>
      Migrated MODx to MODX where applicable<br />
  </div>
    </div>

        <div class="wiki-content">
        </div>

<fieldset class="hidden parameters">
    <legend>Labels parameters</legend>
    <input type="hidden" id="editLabel" value="Edit">
    <input type="hidden" id="addLabel" value="Add Labels">
    <input type="hidden" id="domainName" value="http://oldrtfm.modx.com">
    <input type="hidden" id="pageId" value="{$pageId}">
    <input type="hidden" id="spaceKey" value="{$spaceKey}">
</fieldset>
    </div>
</div>
</body>
</html>
EOT;

        $extractor = new OldRtfmContentExtractor();
        $extractor->extract($source, $this->stats);

        $this->assertValueStat(PageStatistics::SOURCE_PAGE_ID_LABEL, $pageId,
            array(self::WARNING => 0));
        $this->assertValueStat(PageStatistics::SOURCE_PAGE_TITLE_LABEL,
            $pageTitle);
        $this->assertValueStat(PageStatistics::SOURCE_PARENT_PAGE_ID_LABEL,
            $parentPageId);
        $this->assertValueStat(PageStatistics::SOURCE_SPACE_KEY_LABEL,
            $spaceKey);
        $this->assertValueStat(PageStatistics::SOURCE_SPACE_NAME_LABEL,
            $spaceName);
        $this->assertValueStat(PageStatistics::SOURCE_MODIFICATION_INFO_LABEL,
            'Added by Shaun McCormick, last edited by Jay Gilmore on Sep 28, 2012');
    }

    public function testExtractShouldThrowExceptionIfBodyOrHtmlEndTagMissing() {
        $source = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Test</title>
</head>
<body>
    <div class="wiki-content">
        content
    </div>
EOT;

        $extractor = new OldRtfmContentExtractor();
        $this->setExpectedException('\RtfmConvert\RtfmException');
        $extractor->extract($source);
    }

    public function testExtractShouldWarnIfDivUnmatched() {
        $source = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Test</title>
</head>
<body>
    <div class="wiki-content">
        content
</body>
</html>
EOT;

        $extractor = new OldRtfmContentExtractor();
        $extractor->extract($source, $this->stats);
        $this->assertTransformStat('warning: unmatched div(s)', 1,
            array(self::WARNING => 1));
    }

    // see http://oldrtfm.modx.com/display/revolution20/Git+Installation
    public function testExtractShouldGetLabels() {
        $html = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Test</title>
</head>
<body>
    <div class="wiki-content">
        content
    </div>
<fieldset class="hidden parameters">
    <legend>Labels parameters</legend>
    <input type="hidden" id="editLabel" value="Edit">
    <input type="hidden" id="addLabel" value="Add Labels">
    <input type="hidden" id="domainName" value="http://oldrtfm.modx.com">
    <input type="hidden" id="pageId" value="18678547">
    <input type="hidden" id="spaceKey" value="revolution20">
</fieldset>

<div id="labels-section" class="pageSection">
    <div id="default-labels-header" class="section-header">
        <h2 id="labels-section-title" class="section-title ">Labels</h2>
            </div>

    <div class="labels-editor">
        <div id="labelsList">
            <div id="label-7471109" class="confluence-label">
    <a class="label" rel="nofollow" href="/label/revolution20/svn">svn</a>    <span class="remove-label-caption">svn</span>
    <a class="remove-label" href="#">Delete</a>
</div>
<div id="label-13074438" class="confluence-label">
    <a class="label" rel="nofollow" href="/label/revolution20/revolution">revolution</a>    <span class="remove-label-caption">revolution</span>
    <a class="remove-label" href="#">Delete</a>
</div>
<div id="label-26247169" class="confluence-label">
    <a class="label" rel="nofollow" href="/label/revolution20/git">git</a>    <span class="remove-label-caption">git</span>
    <a class="remove-label" href="#">Delete</a>
</div>
<div id="label-26247170" class="confluence-label">
    <a class="label" rel="nofollow" href="/label/revolution20/developer">developer</a>    <span class="remove-label-caption">developer</span>
    <a class="remove-label" href="#">Delete</a>
</div>
<div id="label-26247171" class="confluence-label">
    <a class="label" rel="nofollow" href="/label/revolution20/advanced">advanced</a>    <span class="remove-label-caption">advanced</span>
    <a class="remove-label" href="#">Delete</a>
</div>
        </div>
</body>
</html>
EOT;
        $expected = 'svn, revolution, git, developer, advanced';

        $extractor = new OldRtfmContentExtractor();
        $extractor->extract($html, $this->stats);

        $this->assertValueStat(PageStatistics::SOURCE_LABELS_LABEL, $expected);
    }

    // helper methods
    protected function formatTestData($contentHtml) {
        return sprintf(self::WIKI_CONTENT_FORMAT, $contentHtml);
    }
}
