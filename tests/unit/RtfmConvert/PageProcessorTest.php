<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class PageProcessorTest extends \PHPUnit_Framework_TestCase {

    public function testProcessPageShouldReturnExpectedPageData() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $expectedData = new PageData(\QueryPath::HTML_STUB,
            new PageStatistics());
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')->with($url)
            ->will($this->returnValue($expectedData));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $processor = new PageProcessor($pageLoader, $fileIo);

        $pageData = $processor->processPage($url, 'temp');
        $this->assertEquals($expectedData, $pageData);
    }

    public function testProcessPageShouldSaveProcessedPage() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $dest = 'temp.html';
        $pageData = new PageData('<html></html>');
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(true));

        $fileIo->expects($this->at(1))->method('write')
            ->with($dest, '<html></html>');
        $processor = new PageProcessor($pageLoader, $fileIo);

        $processor->processPage($url, $dest);
    }

    /**
     * @depends testProcessPageShouldSaveProcessedPage
     */
    public function testProcessPageShouldCreateDirIfNotExist() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $dest = 'path/to/temp.html';
        $pageData = new PageData('<html></html>');
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(false));

        $fileIo->expects($this->at(1))->method('mkdir')
            ->with('path/to');
        $fileIo->expects($this->at(2))->method('write')
            ->with($dest, '<html></html>');
        $processor = new PageProcessor($pageLoader, $fileIo);

        $processor->processPage($url, $dest);
    }

    /**
     * @depends testProcessPageShouldSaveProcessedPage
     */
    public function testProcessPageShouldSaveStats() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $dest = 'temp.html';
        $statsDest = 'temp.html.json';
        $stats = new PageStatistics();
        $stats->addValueStat('stat', 1);
        $pageData = new PageData('<html></html>', $stats);
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(true));

        $fileIo->expects($this->at(2))->method('write')
            ->with($this->equalTo($statsDest),
                $this->stringStartsWith('{"stat":{"value":1}'));
        $processor = new PageProcessor($pageLoader, $fileIo);

        $processor->processPage($url, $dest);
    }

    public function testProcessPageShouldProcessExpectedOperation() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $pageData = new PageData('');
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $operation = $this->getMock('\RtfmConvert\ProcessorOperationInterface');

        $operation->expects($this->once())->method('process')
            ->will($this->returnValue($pageData));
        $processor = new PageProcessor($pageLoader, $fileIo);
        $processor->register($operation);

        $processor->processPage($url, 'temp');
    }

    /**
     * @depends testProcessPageShouldProcessExpectedOperation
     */
    public function testProcessPageShouldProcessExpectedOperations() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $pageData1 = new PageData('<p>1</p>');
        $pageData2 = new PageData('<p>2</p>');
        $pageData3 = new PageData('<p>3</p>');
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData1));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $operation1 = $this->getMock('\RtfmConvert\ProcessorOperationInterface');
        $operation2 = $this->getMock('\RtfmConvert\ProcessorOperationInterface');

        $operation1->expects($this->once())->method('process')
            ->with($pageData1)->will($this->returnValue($pageData2));
        $operation2->expects($this->once())->method('process')
            ->with($pageData2)->will($this->returnValue($pageData3));
        $processor = new PageProcessor($pageLoader, $fileIo);
        $processor->register($operation1);
        $processor->register($operation2);

        $processor->processPage($url, 'temp');
    }

    public function testProcessPageShouldAddErrorOnException() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')->with($url)
            ->will($this->throwException(new RtfmException()));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $processor = new PageProcessor($pageLoader, $fileIo);

        $pageData = $processor->processPage($url, 'temp');
        $statsArray = $pageData->getStats()->getStats();
        $this->assertArrayHasKey('Errors', $statsArray);
    }
}
