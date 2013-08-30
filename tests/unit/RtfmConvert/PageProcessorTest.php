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
        $pageLoader = $this->getMock('\RtfmConvert\PageLoader');
        $pageLoader->expects($this->any())->method('getData')->with($url)
            ->will($this->returnValue($expectedData));
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $processor = new PageProcessor($pageLoader, $fileIo);

        $pageData = $processor->processPage($url, 'temp');
        $this->assertEquals($expectedData, $pageData);
    }

    public function testProcessPageShouldSaveProcessedPage() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $dest = 'temp';
        $pageData = new PageData('');
        $pageLoader = $this->getMock('\RtfmConvert\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\FileIo');

        $fileIo->expects($this->once())->method('write')
            ->with($dest, $pageData);
        $processor = new PageProcessor($pageLoader, $fileIo);

        $processor->processPage($url, $dest);
    }

    public function testProcessPageShouldProcessExpectedOperation() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $pageData = new PageData('');
        $pageLoader = $this->getMock('\RtfmConvert\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData));
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $operation = $this->getMock('\RtfmConvert\ProcessorOperationInterface');

        $operation->expects($this->once())->method('process');
        $processor = new PageProcessor($pageLoader, $fileIo);
        $processor->register($operation);

        $processor->processPage($url, 'temp');
    }

    public function testProcessPageShouldProcessExpectedOperations() {
        $url = 'http://oldrtfm.modx.com/display/revolution20/Getting+Started';
        $pageData1 = new PageData('<p>1</p>');
        $pageData2 = new PageData('<p>2</p>');
        $pageLoader = $this->getMock('\RtfmConvert\PageLoader');
        $pageLoader->expects($this->any())->method('getData')
            ->will($this->returnValue($pageData1));
        $fileIo = $this->getMock('\RtfmConvert\FileIo');
        $operation1 = $this->getMock('\RtfmConvert\ProcessorOperationInterface');
        $operation2 = $this->getMock('\RtfmConvert\ProcessorOperationInterface');

        $operation1->expects($this->once())->method('process')
            ->with($pageData1)->will($this->returnValue($pageData2));
        $operation2->expects($this->once())->method('process')
            ->with($pageData2);
        $processor = new PageProcessor($pageLoader, $fileIo);
        $processor->register($operation1);
        $processor->register($operation2);

        $processor->processPage($url, 'temp');
    }
}
