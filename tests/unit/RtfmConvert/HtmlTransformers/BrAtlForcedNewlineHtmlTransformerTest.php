<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class BrAtlForcedNewlineHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

    public function testTransformShouldRemoveFirstBrAtlForcedNewline() {
        $expected = '<p>Welcome</p>';
        $html = <<<EOT
<p><br class="atl-forced-newline" /></p>
{$expected}
EOT;

        $pageData = new PageData($html);
        $transformer = new BrAtlForcedNewlineHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldRemoveLastBrAtlForcedNewline() {
        $expected = '<h3><a name="BasicInstallation-SeeAlso"></a>See Also</h3>';
        $html = <<<EOT
{$expected}
<p><br class="atl-forced-newline" /></p>
EOT;

        $pageData = new PageData($html);
        $transformer = new BrAtlForcedNewlineHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldRemoveAtlForcedNewlineClassInP() {
        $html = <<<'EOT'
<p>Welcome</p>
<p><br class="atl-forced-newline" /></p>
<h3><a name="BasicInstallation-SeeAlso"></a>See Also</h3>
EOT;

        $expected = <<<'EOT'
<p>Welcome</p>
<p><br /></p>
<h3><a name="BasicInstallation-SeeAlso"></a>See Also</h3>
EOT;

        $pageData = new PageData($html);
        $transformer = new BrAtlForcedNewlineHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldRemoveAtlForcedNewlineClassInTable() {
        $html = <<<'EOT'
<table><tr><td>[[*field]]
<br class="atl-forced-newline" /></td></tr></table>
EOT;

        $expected = <<<'EOT'
<table><tr><td>[[*field]]
<br /></td></tr></table>
EOT;

        $pageData = new PageData($html);
        $transformer = new BrAtlForcedNewlineHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }
}
