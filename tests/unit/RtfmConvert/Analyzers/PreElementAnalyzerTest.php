<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class PreElementAnalyzerTest extends HtmlTestCase {

    public function testProcessShouldAddExpectedPreCount() {
        $html = <<<'EOT'
<html>
<head><title>Test</title></head>
<body>
<pre></pre>
</body>
</html>
EOT;

        $pageData = new PageData($html, $this->stats);
        $analyzer = new PreElementAnalyzer('before: ');
        $analyzer->process($pageData);

        $this->assertTransformStat('before: pre elements', 1);
    }

    public function testProcessShouldNotWarnWhenPreCountsMatch() {
        $html = <<<'EOT'
<html>
<head><title>Test</title></head>
<body>
<pre></pre>
</body>
</html>
EOT;

        $pageData = new PageData($html, $this->stats);
        $pageData->addTransformStat('before: pre elements', 1);
        $analyzer = new PreElementAnalyzer('after: ', 'before: ');
        $analyzer->process($pageData);

        $this->assertTransformStat('after: pre elements', 1,
            array(self::WARNING => 0));
    }

    public function testProcessShouldErrorWhenPreCountsDiffer() {
        $html = <<<'EOT'
<html>
<head><title>Test</title></head>
<body>
<pre></pre>
</body>
</html>
EOT;

        $pageData = new PageData($html, $this->stats);
        $pageData->addTransformStat('before: pre elements', 2);
        $analyzer = new PreElementAnalyzer('after: ', 'before: ');
        $analyzer->process($pageData);

        $this->assertTransformStat('after: pre elements', 1,
            array(PageStatistics::ERROR => 1));
    }
}
