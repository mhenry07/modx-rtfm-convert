<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;

class CharsetDeclarationTextTransformerTest extends \PHPUnit_Framework_TestCase {

    public function testTransformShouldInjectMetaCharset() {
        $input = <<<'EOT'
<html>
<head>
<title></title>
</head>
<body></body>
</html>
EOT;
        $expected = <<<'EOT'
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>
<body></body>
</html>
EOT;
        $pageData = new PageData($input);
        $transformer = new CharsetDeclarationTextTransformer();
        $result = $transformer->transform($pageData);
        $this->assertEquals($expected, $result);
    }

    public function testTransformShouldNotInjectMetaCharsetIfDeclared() {
        $input = <<<'EOT'
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title></title>
</head>
<body></body>
</html>
EOT;
        $expected = $input;
        $pageData = new PageData($input);
        $transformer = new CharsetDeclarationTextTransformer();
        $result = $transformer->transform($pageData);
        $this->assertEquals($expected, $result);
    }
}
