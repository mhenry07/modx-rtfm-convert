<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;

class ExternalLinkHtmlTransformerTest extends HtmlTestCase {

// see http://oldrtfm.modx.com/display/revolution20/Installation
    public function testTransformShouldCleanUpExternalLinks() {
        $input = <<<'EOT'
<p>... from the <a href="http://modxcms.com/download/" class="external-link" rel="nofollow">MODX Downloads</a> page.</p>
EOT;
        $expected = <<<'EOT'
<p>... from the <a href="http://modxcms.com/download/">MODX Downloads</a> page.</p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ExternalLinkHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('a.external-link', 1,
            array(self::TRANSFORM => true));
    }
}
