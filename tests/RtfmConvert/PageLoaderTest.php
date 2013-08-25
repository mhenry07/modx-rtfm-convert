<?php
/**
 * User: mhenry
 * Date: 8/23/13
 * Time: 6:53 PM
 */

namespace RtfmConvert;

class PageLoaderTest extends \PHPUnit_Framework_TestCase {
    const DATA_DIR = '../../data/test/';
    const RTFM_MODX_COM = 'http://rtfm.modx.com/';

    /** @var PageLoader */
    private $pageLoader;

    private $tempFile;

    public function setUp() {
        $this->pageLoader = new PageLoader();
        if (!file_exists(self::DATA_DIR))
            mkdir(self::DATA_DIR, 0777, true);
    }

    public function tearDown() {
        if (isset($this->tempFile) && file_exists($this->tempFile))
            unlink($this->tempFile);
    }

    public function testGetShouldRetrieveWebPage() {
        $this->requireWorkingUrl(self::RTFM_MODX_COM);
        $page = $this->pageLoader->get(self::RTFM_MODX_COM);
        $this->assertInternalType('string', $page);
        $this->assertContains('<html', $page);
    }

    public function testGetUrlShouldThrowRtfmExceptionOnError() {
        $this->setExpectedException('\RtfmConvert\RtfmException');
        $this->pageLoader->get('http://localhost/invalid-url');
    }

    public function testGetWithCacheFileShouldLoadCacheFile() {
        $this->writeTempFile('PageLoader.get', 'local');

        $page = $this->pageLoader->get(self::RTFM_MODX_COM, $this->tempFile);
        $this->assertStringEqualsFile($this->tempFile, $page);
    }

    public function testGetWithCacheFileShouldLoadUrlWhenFileNotFound() {
        $filename = 'non-existent-file';
        $this->requireFileNotExist($filename);
        $this->requireWorkingUrl(self::RTFM_MODX_COM);

        $page = $this->pageLoader->get(self::RTFM_MODX_COM, $filename);
        $this->assertContains('<html', $page);
    }

    // helper methods
    protected function requireWorkingUrl($url) {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $retcode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($retcode == 200 || $retcode >= 300 && $retcode < 400)
            return;
        $this->markTestSkipped("Test requires that the URL '{$url}' works. (Status code: {$retcode})");
    }

    protected function requireFileNotExist($filename) {
        if (file_exists($filename))
            $this->markTestSkipped("Test requires that the file '{$filename}' not exist.");
    }

    protected function writeTempFile($filename, $contents) {
        $this->tempFile = self::DATA_DIR . $filename;
        file_put_contents($this->tempFile, $contents);
    }
}
