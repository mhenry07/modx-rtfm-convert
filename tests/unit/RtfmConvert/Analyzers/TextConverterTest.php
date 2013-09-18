<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class TextConverterTest extends \PHPUnit_Framework_TestCase {

    public function testProcessShouldConvertHtmlToText() {
        $html = <<<'EOT'
<html>
<head><title>Test</title></head>
<body>
    <h1>
        Heading 1
    </h1>

    <p>
        Here is some content
    </p>
    <h2>Heading 2</h2><ul><li>item 1</li><li>item 2</li></ul>
</body>
</html>
EOT;

        $expectedText = <<<'EOT'
Heading 1
Here is some content
Heading 2
item 1
item 2
EOT;

        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(true));
        $fileIo->expects($this->any())->method('write')
            ->with('../data/text/page/path/before.txt', $expectedText);

        $stats = new PageStatistics();
        $stats->addValueStat(PageStatistics::PATH_LABEL, '/page/path');
        $pageData = new PageData($html, $stats);

        $converter = new TextConverter($fileIo);
        $converter->setBasePath('../data/text');
        $converter->setName('before');
        $result = $converter->process($pageData);
    }


    public function testProcessShouldWriteTextFileForPathWithQueryString() {
        $html = <<<'EOT'
<html>
<head><title>Test</title></head>
<body>
    <h1>Heading 1</h1>
    <p>Here is some content</p>
</body>
</html>
EOT;

        $expectedText = <<<'EOT'
Heading 1
Here is some content
EOT;

        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(true));
        $fileIo->expects($this->any())->method('write')->with(
            '../data/text/pages/viewpage.action/pageId=43417765/before.txt',
            $expectedText);

        $stats = new PageStatistics();
        $stats->addValueStat(PageStatistics::PATH_LABEL,
            '/pages/viewpage.action?pageId=43417765');
        $pageData = new PageData($html, $stats);

        $converter = new TextConverter($fileIo);
        $converter->setBasePath('../data/text');
        $converter->setName('before');
        $result = $converter->process($pageData);
    }

    public function testProcessShouldHandleEspanol() {
        $html = '<h1>YAMS: Documentación en Español</h1>';

        $expectedText = 'YAMS: Documentación en Español';

        $fileIo = $this->getMock('\RtfmConvert\Infrastructure\FileIo');
        $fileIo->expects($this->any())->method('exists')
            ->will($this->returnValue(true));
        $fileIo->expects($this->any())->method('write')
            ->with('/page/path/before.txt', $expectedText);

        $stats = new PageStatistics();
        $stats->addValueStat(PageStatistics::PATH_LABEL, '/page/path');
        $pageData = new PageData($html, $stats);

        $converter = new TextConverter($fileIo);
        $converter->setName('before');
        $result = $converter->process($pageData);
    }
}
