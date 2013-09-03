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

        $fileIo->expects($this->at(0))->method('write')
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
        $stats->add('stat', 1, true, false);
        $pageData = new PageData('<html></html>', $stats);
        $pageLoader = $this->getMock('\RtfmConvert\Infrastructure\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');

        $fileIo->expects($this->at(1))->method('write')
            ->with($statsDest, '{"stat":{"label":"stat","value":1,"transformed":true,"warning":false}}');
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
}
