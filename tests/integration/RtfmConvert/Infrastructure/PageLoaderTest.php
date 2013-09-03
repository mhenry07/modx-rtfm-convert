<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Infrastructure;


use RtfmConvert\PageStatistics;

class PageLoaderTest extends \PHPUnit_Framework_TestCase {
    const DATA_DIR = '../data/test/';
    const RTFM_MODX_COM = 'http://rtfm.modx.com/';

    /** @var PageLoader */
    private $pageLoader;
    /** @var PageStatistics */
    private $stats;

    private $tempFile;

    public function setUp() {
        $this->stats = new PageStatistics();
        $this->pageLoader = new PageLoader(new CurlWrapper(), new FileIo());
        if (!file_exists(self::DATA_DIR))
            mkdir(self::DATA_DIR, 0777, true);
    }

    public function tearDown() {
        echo '\nStats: ', print_r($this->stats->getStats());
        if (isset($this->tempFile) && file_exists($this->tempFile))
            unlink($this->tempFile);
    }

    public function testGetShouldRetrieveWebPage() {
        $this->requireWorkingUrl(self::RTFM_MODX_COM);
        $page = $this->pageLoader->get(self::RTFM_MODX_COM, $this->stats);
        $this->assertInternalType('string', $page);
        $this->assertContains('</html>', $page);
    }

    public function testGetShouldRetrieveLocalFile() {
        $expected = 'local';
        $this->tempFile = self::DATA_DIR . 'file.txt';
        file_put_contents($this->tempFile, $expected);

        $result = $this->pageLoader->get($this->tempFile, $this->stats);
        $this->assertEquals($expected, $result);
    }

    /**
     * @depends testGetShouldRetrieveWebPage
     */
    public function testGetShouldRetrieveRedirectedWebPage() {
        $url = "http://rtfm.modx.com/display/revolution20/Tag+Syntax";
        $this->requireWorkingUrl($url);
        $page = $this->pageLoader->get($url, $this->stats);
        $this->assertInternalType('string', $page);
        $this->assertContains('</html>', $page);
    }

    public function testGetShouldThrowRtfmExceptionWhenPageNotFound() {
        $this->setExpectedException('\RtfmConvert\RtfmException');
        $this->pageLoader->get('http://rtfm.modx.com/invalid-page', $this->stats);
    }

    public function testGetShouldThrowRtfmExceptionWhenPageIncomplete() {
        $curl = $this->getMock('\RtfmConvert\Infrastructure\CurlWrapper');
        $curl->expects($this->any())->method('create')
            ->will($this->returnValue($curl));
        $curl->expects($this->any())->method('exec')
            ->will($this->returnValue('<htm'));
        $curl->expects($this->any())->method('setinfoArray')
            ->will($this->returnValue(true));
        $getinfoMap = array(
            array(CURLINFO_HTTP_CODE, 200),
            array(CURLINFO_CONTENT_LENGTH_DOWNLOAD, 999)
        );
        $curl->expects($this->any())->method('getinfo')
            ->will($this->returnValueMap($getinfoMap));

        $pageLoader = new PageLoader($curl);
        $this->setExpectedException('\RtfmConvert\RtfmException');
//            'downloaded size does not match Content-Length header');
        $pageLoader->get(
            'http://oldrtfm.modx.com/display/revolution20/Tag+Syntax',
            $this->stats);
    }

    /**
     * @depends testGetShouldRetrieveWebPage
     */
    public function testGetDataShouldGetExpectedData() {
        $this->pageLoader = new PageLoader(new CurlWrapper());
        $result = $this->pageLoader->getData(self::RTFM_MODX_COM, $this->stats);
        $this->assertContains('</html>', $result->getHtmlString());
        $this->assertEquals($this->stats, $result->getStats());
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
}
