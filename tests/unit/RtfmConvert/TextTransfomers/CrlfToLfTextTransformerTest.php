<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert;


use RtfmConvert\TextTransformers\CrlfToLfTextTransformer;

class CrlfToLfTextTransformerTest extends \PHPUnit_Framework_TestCase {
    /** @var CrlfToLfTextTransformer */
    protected $transformer;

    protected function setUp() {
        $this->transformer = new CrlfToLfTextTransformer();
    }

    public function testTransformEmptyShouldReturnEmpty() {
        $result = $this->transformer->transform('');
        $this->assertEquals('', $result);
    }

    public function testTransformTextShouldReturnText() {
        $result = $this->transformer->transform('text');
        $this->assertEquals('text', $result);
    }

    public function testTransformCrlfShouldReturnLf() {
        $result = $this->transformer->transform("\r\n");
        $this->assertEquals("\n", $result);
    }

    public function testTransformTextWithCrlfShouldReturnTextWithLf() {
        $result = $this->transformer->transform("text\r\n");
        $this->assertEquals("text\n", $result);
    }

    public function testTransformTwoCrlfsShouldReturnTwoLfs() {
        $result = $this->transformer->transform("\r\n\r\n");
        $this->assertEquals("\n\n", $result);
    }

    public function testTransformComplexStringShouldReturnExpectedString() {
        $result = $this->transformer->transform(
            "text on:\tline 1\r\ntext on:   line 2\r\n");
        $this->assertEquals("text on:\tline 1\ntext on:   line 2\n", $result);
    }
}
