<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;

class ImageHtmlTransformerTest extends HtmlTestCase {

    // e.g. http://oldrtfm.modx.com/display/revolution20/Basic+Installation
    public function testTransformShouldTransformImageWrapSpan() {
        $input = <<<'EOT'
<p><span class="image-wrap" style=""><img src="/download/attachments/18678053/setup-opt1.png?version=2&amp;modificationDate=1280259765000" style="border: 0px solid black" /></span></p>
EOT;
        $expected = <<<'EOT'
<p><img src="/download/attachments/18678053/setup-opt1.png?version=2&amp;modificationDate=1280259765000" /></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('img[style="border: 0px solid black"]', 1, true);
        $this->assertStat('span.image-wrap[style=""]', 1, true);
    }

    // e.g. http://oldrtfm.modx.com/display/revolution20/Using+Friendly+URLs
    public function testTransformShouldPreserveBorder() {
        $input = <<<'EOT'
<p><span class="image-wrap" style=""><img src="/download/attachments/18678057/shawnwilkerson_09_01.jpg?version=1&amp;modificationDate=1299237799000" style="border: 1px solid black" /></span></p>
EOT;
        $expected = <<<'EOT'
<p><img src="/download/attachments/18678057/shawnwilkerson_09_01.jpg?version=1&amp;modificationDate=1299237799000"" style="border: 1px solid black" /></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('img[style="border: 0px solid black"]', 0, false);
    }

    // e.g. http://oldrtfm.modx.com/display/revolution20/An+Overview+of+MODX
    public function testTransformShouldPreserveImageWrapWithStyle() {
        $input = <<<'EOT'
<p><span class="image-wrap" style="float: right"><img src="/download/attachments/18678475/avgjoe.png?version=1&amp;modificationDate=1280336319000" style="border: 0px solid black" /></span></p>
EOT;
        $expected = <<<'EOT'
<p><span class="image-wrap" style="float: right"><img src="/download/attachments/18678475/avgjoe.png?version=1&amp;modificationDate=1280336319000" /></span></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('span.image-wrap[style=""]', 0, false);
    }

    /**
     * e.g. http://oldrtfm.modx.com/display/revolution20/Basic+Installation
     * @depends testTransformShouldTransformImageWrapSpan
     */
    public function testTransformShouldHandleMultipleImages() {
        $input = <<<'EOT'
<p><span class="image-wrap" style=""><img src="/download/attachments/18678053/setup-db2.png?version=1&amp;modificationDate=1280264244000" style="border: 0px solid black" /></span></p>
<p>For most users you can leave these values at what they are. However, if you need to change them, <b>make sure</b> the collation matches the charset. Click the 'Create or test selection of your database.' after you've finished.</p>
<h3 id="BasicInstallation-CreatinganAdministratorUser">Creating an Administrator User</h3>
<p><span class="image-wrap" style=""><img src="/download/attachments/18678053/setup-db3.png?version=1&amp;modificationDate=1280264231000" style="border: 0px solid black" /></span></p>
EOT;
        $expected = <<<'EOT'
<p><img src="/download/attachments/18678053/setup-db2.png?version=1&amp;modificationDate=1280264244000" /></p>
<p>For most users you can leave these values at what they are. However, if you need to change them, <b>make sure</b> the collation matches the charset. Click the 'Create or test selection of your database.' after you've finished.</p>
<h3 id="BasicInstallation-CreatinganAdministratorUser">Creating an Administrator User</h3>
<p><img src="/download/attachments/18678053/setup-db3.png?version=1&amp;modificationDate=1280264231000" /></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('img[style="border: 0px solid black"]', 2, true);
        $this->assertStat('span.image-wrap[style=""]', 2, true);
    }

    // e.g. http://oldrtfm.modx.com/display/revolution20/Resources
    public function testTransformShouldTransformConfluenceThumbnailLink() {
        $input = <<<'EOT'
<p><span class="image-wrap" style=""><a class="confluence-thumbnail-link 845x703" href='http://oldrtfm.modx.com/download/attachments/18678067/resource-edit1.png'><img src="/download/thumbnails/18678067/resource-edit1.png" style="border: 0px solid black" /></a></span></p>
EOT;
        $expected = <<<'EOT'
<p><a class="confluence-thumbnail-link 845x703" href="/download/attachments/18678067/resource-edit1.png"><img src="/download/thumbnails/18678067/resource-edit1.png" /></a></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat('a.confluence-thumbnail-link[href^="http://oldrtfm.modx.com"]', 1, true);
    }

    /**
     * @depends testTransformShouldTransformImageWrapSpan
     */
    public function testTransformShouldHandleMixedImageWrapStyles() {
        $input = <<<'EOT'
<p><span class="image-wrap" style=""><img src="/download/attachments/18678053/setup-opt1.png?version=2&amp;modificationDate=1280259765000" style="border: 0px solid black" /></span></p>
<p><span class="image-wrap" style="float: right"><img src="/download/attachments/18678475/avgjoe.png?version=1&amp;modificationDate=1280336319000" style="border: 0px solid black" /></span></p>
EOT;
        $expected = <<<'EOT'
<p><img src="/download/attachments/18678053/setup-opt1.png?version=2&amp;modificationDate=1280259765000" /></p>
<p><span class="image-wrap" style="float: right"><img src="/download/attachments/18678475/avgjoe.png?version=1&amp;modificationDate=1280336319000" /></span></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    /**
     * @depends testTransformShouldTransformConfluenceThumbnailLink
     */
    public function testTransformShouldHandleMixedThumbnailLinks() {
        $input = <<<'EOT'
<p><span class="image-wrap" style=""><a class="confluence-thumbnail-link 845x703" href='/download/attachments/18678067/resource-edit1.png'><img src="/download/thumbnails/18678067/resource-edit1.png" style="border: 0px solid black" /></a></span></p>
<p><span class="image-wrap" style=""><a class="confluence-thumbnail-link 845x703" href='http://oldrtfm.modx.com/download/attachments/18678067/resource-edit2.png'><img src="/download/thumbnails/18678067/resource-edit2.png" style="border: 0px solid black" /></a></span></p>
EOT;
        $expected = <<<'EOT'
<p><a class="confluence-thumbnail-link 845x703" href="/download/attachments/18678067/resource-edit1.png"><img src="/download/thumbnails/18678067/resource-edit1.png" /></a></p>
<p><a class="confluence-thumbnail-link 845x703" href="/download/attachments/18678067/resource-edit2.png"><img src="/download/thumbnails/18678067/resource-edit2.png" /></a></p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    // e.g. http://oldrtfm.modx.com/display/revolution20/Adding+Custom+Fields+to+Manager+Forms
    public function testTransformShouldConvertSmileEmoticonToText() {
        $input = <<<'EOT'
<p>...for tutorial purposes <img class="emoticon" src="/images/icons/emoticons/smile.gif" height="20" width="20" align="absmiddle" alt="" border="0"/> .</p>
EOT;
        $expected = <<<'EOT'
<p>...for tutorial purposes :) .</p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat(
            'img.emoticon[src="/images/icons/emoticons/smile.gif"]', 1, true);
    }

    // e.g. http://oldrtfm.modx.com/display/ADDON/modSwiftMailer
    public function testTransformShouldConvertWinkEmoticonToText() {
        $input = <<<'EOT'
<p>Hey you! Go ahead and slap that sucker into a snippet. <img class="emoticon" src="/images/icons/emoticons/wink.gif" height="20" width="20" align="absmiddle" alt="" border="0"/> It will, if you have set up your MODX in the right manner, send you an e-mail with a subject and printed array.</p>
EOT;
        $expected = <<<'EOT'
<p>Hey you! Go ahead and slap that sucker into a snippet. ;) It will, if you have set up your MODX in the right manner, send you an e-mail with a subject and printed array.</p>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ImageHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertStat(
            'img.emoticon[src="/images/icons/emoticons/wink.gif"]', 1, true);
    }
}
