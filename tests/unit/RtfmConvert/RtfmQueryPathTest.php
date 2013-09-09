<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class RtfmQueryPathTest extends \PHPUnit_Framework_TestCase {

    public function testHtmlqpAndGetHtmlStringShouldNotSelfCloseInnerEmptyDiv() {
        $input = '<div class="outer"><div class="inner"></div></div>';
        $expected = <<<'EOT'
<div class="outer">
  <div class="inner"></div>
</div>
EOT;

        $qp = RtfmQueryPath::htmlqp($input, 'div.outer');
        $result = RtfmQueryPath::getHtmlString($qp);
        $this->assertEquals($expected, $result);
    }

    public function testHtmlqpAndGetHtmlStringShouldNotAddClosingBrTag() {
        $input = '<br /><br>';

        $qp = RtfmQueryPath::htmlqp($input, 'body');
        $result = RtfmQueryPath::getHtmlString($qp);
        $this->assertNotContains('</br>', $result);
    }

    public function testHtmlqpAndGetHtmlStringShouldNotOutputCrEntities() {
        $input = "<p>\r\n</p>";

        $qp = RtfmQueryPath::htmlqp($input, 'p');
        $result = RtfmQueryPath::getHtmlString($qp);
        $this->assertNotContains('&#13;', $result);
    }

    public function testHtmlqpAndGetHtmlStringShouldHandleHtml401StrictDoctype() {
        $input = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Title</title></head>
<body><p>content</p></body>
</html>
EOT;

        $expected = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
  <head>
    <title>Title</title>
  </head>
  <body>
    <p>content</p>
  </body>
</html>
EOT;

        $qp = RtfmQueryPath::htmlqp($input, ':root');
        $result = RtfmQueryPath::getHtmlString($qp);
        $this->assertEquals($expected, $result);
    }

    public function testCountAllShouldReturnExpectedCount() {
        $input = <<<'EOT'
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Title</title></head>
<body>
<h1>heading</h1>
<p>content <span>inner</span></p>
</body>
</html>
EOT;

        $qp = RtfmQueryPath::htmlqp($input, 'body');
        $result = RtfmQueryPath::countAll($qp);
        $this->assertEquals(3, $result);
    }
}
