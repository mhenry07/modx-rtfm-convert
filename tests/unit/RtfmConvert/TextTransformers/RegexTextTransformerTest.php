<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


class RegexTextTransformerTest extends \PHPUnit_Framework_TestCase {

    public function testTransformShouldReplacePattern() {
        $transformer = new RegexTextTransformer('/\r\n/', "\n");
        $result = $transformer->transform("as\r\ndf\r\n");
        $this->assertEquals("as\ndf\n", $result);
    }

    public function testTransformShouldReplacePatternArray() {
        $transformer = new RegexTextTransformer(
            array('/\[/', '/]/'),
            array('&#91;', '&#93;'));
        $result = $transformer->transform('[[asdf]]');
        $this->assertEquals('&#91;&#91;asdf&#93;&#93;', $result);
    }
}
