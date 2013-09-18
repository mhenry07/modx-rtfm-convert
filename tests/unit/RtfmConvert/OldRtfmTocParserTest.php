<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class OldRtfmTocParserTest extends \PHPUnit_Framework_TestCase {
    protected $pageEntryHtmlFormat = <<<'EOT'
<div class="plugin_pagetree_children_content">
    <span class="plugin_pagetree_children_span" id="childrenspan18678198-0">
        <a href="%s">%s</a>
    </span>
</div>
EOT;

    protected $toggleHtml = <<<'EOT'
<div class="plugin_pagetree_childtoggle_container">
    <a id="plusminus18678198-0" class="plugin_pagetree_childtoggle icon icon-minus" href="#">
    </a>
</div>
EOT;

    public function testParseTocFileShouldGetExpectedHref() {
        $filename = 'toc.html';
        $expectedHref = '/display/revolution20/Getting+Started';
        $expected = array(
            'href' => $expectedHref,
            'title' => 'Getting Started',
            'url' => "http://oldrtfm.modx.com{$expectedHref}",
            'source' => $filename
        );

        $html = sprintf($this->pageEntryHtmlFormat, $expectedHref,
            $expected['title']);

        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('read')
            ->will($this->returnValue($html));

        $parser = new OldRtfmTocParser($fileIo);
        $parser->setBaseUrl('http://oldrtfm.modx.com');
        $result = $parser->parseTocFile($filename);
        $this->assertEquals($expected, $result[0]);
    }

    public function testParseTocFileShouldIgnoreToggle() {
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('read')
            ->will($this->returnValue($this->toggleHtml));

        $parser = new OldRtfmTocParser($fileIo);
        $parser->setBaseUrl('http://oldrtfm.modx.com');
        $result = $parser->parseTocFile('toc.html');
        $this->assertEquals(0, count($result));
    }

    public function testParseTocDirectoryShouldGetExpected() {
        $tocFiles = array('toc1.html', 'toc2.html');
        $expected = array(
            array('href' => '/1', 'title' => 'Page 1', 'url' => "http://oldrtfm.modx.com/1", 'source' => $tocFiles[0]),
            array('href' => '/2', 'title' => 'Page 2', 'url' => "http://oldrtfm.modx.com/2", 'source' => $tocFiles[1])
        );
        $readMap = array(
            array($tocFiles[0], sprintf($this->pageEntryHtmlFormat, '/1', 'Page 1')),
            array($tocFiles[1], sprintf($this->pageEntryHtmlFormat, '/2', 'Page 2'))
        );
        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('findPathnames')
            ->will($this->returnValue($tocFiles));
        $fileIo->expects($this->any())->method('read')
            ->will($this->returnValueMap($readMap));

        $parser = new OldRtfmTocParser($fileIo);
        $parser->setBaseUrl('http://oldrtfm.modx.com');
        $result = $parser->parseTocDirectory('/path/to/toc');
        $this->assertEquals($expected, $result);
    }
}
