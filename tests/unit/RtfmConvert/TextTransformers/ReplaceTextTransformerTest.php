<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


class ReplaceTextTransformerTest extends \PHPUnit_Framework_TestCase {

    public function testTransformShouldReplaceSearch() {
        $transformer = new ReplaceTextTransformer("\r\n", "\n");
        $result = $transformer->transform("as\r\ndf\r\n");
        $this->assertEquals("as\ndf\n", $result);
    }

    public function testTransformShouldReplaceSearchArray() {
        $transformer = new ReplaceTextTransformer(
            array('[', ']'),
            array('&#91;', '&#93;'));
        $result = $transformer->transform("[[asdf]]");
        $this->assertEquals("&#91;&#91;asdf&#93;&#93;", $result);
    }
}
