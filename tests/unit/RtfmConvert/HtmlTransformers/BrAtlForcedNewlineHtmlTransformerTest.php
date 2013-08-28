<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


class BrAtlForcedNewlineHtmlTransformerTest extends \RtfmConvert\HtmlTestCase {

    public function testTransformShouldRemoveFirstBrAtlForcedNewline() {
        $expected = '<p>Welcome</p>';
        $html = <<<EOT
<p><br class="atl-forced-newline" /></p>
{$expected}
EOT;

        $transformer = new BrAtlForcedNewlineHtmlTransformer($html);
        $result = $transformer->transform();
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldRemoveLastBrAtlForcedNewline() {
        $expected = '<h3><a name="BasicInstallation-SeeAlso"></a>See Also</h3>';
        $html = <<<EOT
{$expected}
<p><br class="atl-forced-newline" /></p>
EOT;

        $transformer = new BrAtlForcedNewlineHtmlTransformer($html);
        $result = $transformer->transform();
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

        $transformer = new BrAtlForcedNewlineHtmlTransformer($html);
        $result = $transformer->transform();
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

        $transformer = new BrAtlForcedNewlineHtmlTransformer($html);
        $result = $transformer->transform();
        $this->assertHtmlEquals($expected, $result);
    }
}
