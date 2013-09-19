<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


class HtmlTidyTextTransformerTest extends \PHPUnit_Framework_TestCase {

    public function testHtmlTidyShouldReturnFormattedHtml() {
        $source = <<<EOT
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
            <title>Title</title>

</head>
<body
    >
<p>text
</p>
</body>
</html>
EOT;

        $expected = <<<EOT
<!DOCTYPE html>
<html>
<head>
<title>Title</title>
</head>
<body>
<p>text</p>
</body>
</html>
EOT;

        $transformer = new HtmlTidyTextTransformer();
        $result = $transformer->transform($source);
        $this->assertEquals($expected, $result);
    }
}
