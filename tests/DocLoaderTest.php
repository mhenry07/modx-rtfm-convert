<?php
/**
 * User: mhenry
 * Date: 8/23/13
 * Time: 6:53 PM
 */

namespace RtfmConvert;

class DocLoaderTest extends \PHPUnit_Framework_TestCase {

    public function testGetShouldRetrieveWebPage() {
        $docLoader = new DocLoader();

        $page = $docLoader->get('http://www.google.com/');
        $this->assertInternalType('string', $page);
        $this->assertStringStartsWith('<!doctype html>', $page);
    }
}
