<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class TextDiffAnalyzerTest extends \PHPUnit_Framework_TestCase {

    public function testProcessShouldCompareMatchingFiles() {
        $expectedStat = array(PageStatistics::VALUE => 0);

        $file1 = 'path/to/before.txt';
        $file2 = 'path/to/after.txt';
        $text1 = 'a';
        $text2 = 'a';
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->at(0))->method('read')->with($file1)
            ->will($this->returnValue($text1));
        $fileIo->expects($this->at(1))->method('read')->with($file2)
            ->will($this->returnValue($text2));

        $stats = new PageStatistics();
        $pageData = new PageData('', $stats);
        $pageData->addValueStat(TextConverter::getLabel('before'), $file1);
        $pageData->addValueStat(TextConverter::getLabel('after'), $file2);

        $analyzer = TextDiffAnalyzer::create('before', 'after', '../data/text',
            $fileIo);
        $result = $analyzer->process($pageData);

        $this->assertArrayHasKey('text diff: before after', $stats->getStats());
        $stat = $stats->getStat('text diff: before after');
        $this->assertArrayValueEquals(0, PageStatistics::VALUE, $stat);
        $this->assertArrayNotHasKey(PageStatistics::WARNING, $stat);
    }

    public function testProcessShouldCompareDifferingFiles() {
        $file1 = 'path/to/before.txt';
        $file2 = 'path/to/after.txt';
        $diffFile = '..\data\text\path\to\before-after.txt.diff';
        $text1 = 'a';
        $text2 = 'b';
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->at(0))->method('read')->with($file1)
            ->will($this->returnValue($text1));
        $fileIo->expects($this->at(1))->method('read')->with($file2)
            ->will($this->returnValue($text2));

        $stats = new PageStatistics();
        $pageData = new PageData('', $stats);
        $pageData->addValueStat(PageStatistics::PATH_LABEL, 'path/to');
        $pageData->addValueStat(TextConverter::getLabel('before'), $file1);
        $pageData->addValueStat(TextConverter::getLabel('after'), $file2);

        $analyzer = TextDiffAnalyzer::create('before', 'after', '../data/text',
            $fileIo);
        $result = $analyzer->process($pageData);

        $this->assertArrayHasKey('text diff: before after', $stats->getStats());
        $stat = $stats->getStat('text diff: before after');
        $this->assertArrayValueEquals(2, PageStatistics::VALUE, $stat);
        $this->assertArrayValueEquals(2, PageStatistics::WARNING, $stat);
        $expectedData = array('insertions (+)' => 1, 'deletions (-)' => 1,
            'filename' => $diffFile);
        $this->assertArrayValueEquals($expectedData, PageStatistics::DATA,
            $stat);
    }

    public function testProcessShouldWriteDiffFile() {
        $diffFile = '../data/text/path/to/before-after.txt.diff';
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('read')
            ->will($this->onConsecutiveCalls('a', 'b'));
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(true));
        $fileIo->expects($this->at(3))->method('write')
            ->with($diffFile);

        $stats = new PageStatistics();
        $pageData = new PageData('', $stats);
        $pageData->addValueStat(PageStatistics::PATH_LABEL, 'path/to');

        $analyzer = TextDiffAnalyzer::create('before', 'after', '../data/text',
            $fileIo);
        $result = $analyzer->process($pageData);
    }

    // helper methods
    public function assertArrayValueEquals($expected, $key, array $array) {
        $this->assertArrayHasKey($key, $array);
        $this->assertEquals($expected, $array[$key]);
    }
}
