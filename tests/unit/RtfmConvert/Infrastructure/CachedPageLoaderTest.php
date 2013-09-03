<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Infrastructure;


class CachedPageLoaderTest extends \PHPUnit_Framework_TestCase {
    const BASE_CACHE_DIR = '/path/to/';

    public function testGetWhenCacheFileNotExistShouldCallBaseGetAndReturnExpectedHtml() {
        $url = 'http://oldrtfm.modx.com/';
        $expected = '<html></html>';
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->onConsecutiveCalls(false, true));
        $basePageLoader = $this->getMock('PageLoaderInterface', array('get'));
        $basePageLoader->expects($this->once())->method('get')
            ->with($this->equalTo($url))
            ->will($this->returnValue($expected));

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->get($url);
        $this->assertEquals($expected, $result);
    }

    public function testGetLocalFileShouldCallBaseGetAndReturnExpectedHtml() {
        $url = '/local/path/to/file.html';
        $expected = '<html></html>';
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $fileIo->expects($this->never())->method('write');
        $basePageLoader = $this->getMock('PageLoaderInterface', array('get'));
        $basePageLoader->expects($this->once())->method('get')
            ->with($this->equalTo($url))
            ->will($this->returnValue($expected));

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->get($url);
        $this->assertEquals($expected, $result);
    }

    public function testGetWhenCacheFileExistsShouldNotCallBaseGetAndShouldReturnExpectedHtml() {
        $url = 'http://oldrtfm.modx.com/';
        $expected = '<html></html>';
        $cacheFile = '/path/to/oldrtfm.modx.com.html';
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $fileIo->expects($this->once())->method('exists')->with($cacheFile)
            ->will($this->returnValue(true));
        $fileIo->expects($this->once())->method('read')->with($cacheFile)
            ->will($this->returnValue($expected));
        $basePageLoader = $this->getMock('PageLoaderInterface', array('get'));
        $basePageLoader->expects($this->never())->method('get');

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->get($url);
        $this->assertEquals($expected, $result);
    }

    /**
     * @depends testGetWhenCacheFileNotExistShouldCallBaseGetAndReturnExpectedHtml
     */
    public function testGetWhenCacheFileNotExistShouldWriteCacheFile() {
        $url = 'http://oldrtfm.modx.com/';
        $expected = '<html></html>';
        $cacheFile = '/path/to/oldrtfm.modx.com.html';
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->onConsecutiveCalls(false, true));
        $basePageLoader = $this->getMock('PageLoaderInterface', array('get'));
        $basePageLoader->expects($this->any())->method('get')
            ->will($this->returnValue($expected));
        $fileIo->expects($this->once())->method('write')->with($cacheFile);

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $pageLoader->get($url);
    }

    /**
     * @depends testGetWhenCacheFileNotExistShouldWriteCacheFile
     */
    public function testGetWhenCacheDirectoryNotExistShouldCreateDirectory() {
        $url = 'http://oldrtfm.modx.com/';
        $expected = '<html></html>';
        $cacheFile = '/path/to/oldrtfm.modx.com.html';
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->onConsecutiveCalls(false, false));
        $basePageLoader = $this->getMock('PageLoaderInterface', array('get'));
        $basePageLoader->expects($this->any())->method('get')
            ->will($this->returnValue($expected));
        $fileIo->expects($this->once())->method('mkdir')->with(dirname($cacheFile));
        $fileIo->expects($this->once())->method('write')->with($cacheFile);

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $pageLoader->get($url);
    }

    public function testGetCachePathOldrtfmModxComShouldReturnExpectedPath() {
        $url = 'http://oldrtfm.modx.com/';
        $expected = '/path/to/oldrtfm.modx.com.html';
        $basePageLoader = $this->getMock('PageLoaderInterface');
        $fileIo = $this->getMock('\RtfmConvert\FileIo');

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->getCachePath($url);
        $this->assertEquals($expected, $result);
    }

    public function testGetCachePathGettingStartedShouldReturnExpectedPath() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $expected = '/path/to/oldrtfm.modx.com/display/revolution20/Getting+Started.html';
        $basePageLoader = $this->getMock('PageLoaderInterface');
        $fileIo = $this->getMock('\RtfmConvert\FileIo');

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->getCachePath($url);
        $this->assertEquals($expected, $result);
    }

    public function testGetCachePathWithPageIdShouldReturnExpectedPath() {
        $url = 'http://oldrtfm.modx.com/pages/viewpage.action?pageId=13205626';
        $expected = '/path/to/oldrtfm.modx.com/pages/viewpage.action/pageId=13205626.html';
        $basePageLoader = $this->getMock('PageLoaderInterface');
        $fileIo = $this->getMock('\RtfmConvert\FileIo');

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->getCachePath($url);
        $this->assertEquals($expected, $result);
    }

    public function testGetCachePathWithPngShouldReturnExpectedPath() {
        $url = 'http://oldrtfm.modx.com/download/attachments/18678475/avgjoe.png?version=1&modificationDate=1280336319000';
        $expected = '/path/to/oldrtfm.modx.com/download/attachments/18678475/avgjoe.png';
        $basePageLoader = $this->getMock('PageLoaderInterface');
        $fileIo = $this->getMock('\RtfmConvert\FileIo');

        $pageLoader = new CachedPageLoader($basePageLoader, $fileIo);
        $pageLoader->setBaseDirectory(self::BASE_CACHE_DIR);
        $result = $pageLoader->getCachePath($url);
        $this->assertEquals($expected, $result);
    }
}
