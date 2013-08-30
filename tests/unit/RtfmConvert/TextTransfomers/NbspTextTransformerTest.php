<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


class NbspTextTransformerTest extends \PHPUnit_Framework_TestCase {
    /** @var NbspTextTransformer */
    protected $transformer;

    protected function setUp() {
        $this->transformer = new NbspTextTransformer();
    }

    public function testTransformUtf8NbspShouldReturnNbspEntity() {
        $nbsp = html_entity_decode('&nbsp;', ENT_HTML401, 'UTF-8');
        $result = $this->transformer->transform($nbsp);
        $this->assertEquals('&nbsp;', $result);
    }
}
