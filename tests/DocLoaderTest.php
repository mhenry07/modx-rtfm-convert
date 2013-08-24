<?php
/**
 * User: mhenry
 * Date: 8/23/13
 * Time: 6:53 PM
 */

namespace RtfmConvert;

class DocLoaderTest extends \PHPUnit_Framework_TestCase {
    const DATA_DIR = '../data/test/';

    /** @var DocLoader */
    private $docLoader;

    private $tempFile;

    public function setUp() {
        $this->docLoader = new DocLoader();
        if (!file_exists(self::DATA_DIR))
            mkdir(self::DATA_DIR, 0777, true);
    }

    public function tearDown() {
        if (isset($this->tempFile) && file_exists($this->tempFile))
            unlink($this->tempFile);
    }

    public function testGetShouldRetrieveWebPage() {
        $page = $this->docLoader->get('http://www.google.com/');
        $this->assertInternalType('string', $page);
        $this->assertContains('<html', $page);
    }

    public function testGetShouldThrowRtfmExceptionOnError() {
        $this->setExpectedException('\RtfmConvert\RtfmException');
        $this->docLoader->get('http://localhost/invalid-url');
    }

    public function testGetShouldLoadLocalFile() {
        $this->tempFile = self::DATA_DIR . 'DocLoader.get';
        $expectedContents = 'local';
        file_put_contents($this->tempFile, $expectedContents);

        $page = $this->docLoader->get('http://www.google.com/', $this->tempFile);
        $this->assertStringEqualsFile($this->tempFile, $page);
    }

    public function testGetShouldLoadRemotePageWhenFileNotFound() {
        $filename = 'non-existent-file';
        if (file_exists($filename))
            $this->markTestSkipped("This test requires that the file '{$filename}' not exist.");

        $page = $this->docLoader->get('http://www.google.com/', $filename);
        $this->assertContains('<html', $page);
    }
}
