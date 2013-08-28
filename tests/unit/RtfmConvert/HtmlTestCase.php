<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class HtmlTestCase extends \PHPUnit_Framework_TestCase {
    /** @var PageStatistics */
    protected $stats;

    public function setUp() {
        $this->stats = new PageStatistics();
    }

    /**
     * This can take any type that htmlqp() can take for $expectedHtml and
     * $actualHtml. (See qp()):
     *  - A string of XML or HTML (See {@link XHTML_STUB})
     *  - A path on the file system or a URL
     *  - A {@link DOMDocument} object
     *  - A {@link SimpleXMLElement} object.
     *  - A {@link DOMNode} object.
     *  - An array of {@link DOMNode} objects (generally {@link DOMElement} nodes).
     *  - Another {@link QueryPath} object.
     *
     * @see qp()
     * @see \PHPUnit_Framework_Assert::assertTag()
     * @param string|\QueryPath\DOMQuery|\DOMDocument|\SimpleXMLElement|\DOMNode|\DOMNode[] $expectedHtml
     * @param string|\QueryPath\DOMQuery|\DOMDocument|\SimpleXMLElement|\DOMNode|\DOMNode[] $actualHtml
     * @param string $message
     *
     * @todo handle empty string for actualHtml (htmlqp('') returns null)
     */
    protected function assertHtmlEquals($expectedHtml, $actualHtml, $message = '') {
        // prevent objects from being altered
        if (is_object($expectedHtml))
            $expectedHtml = clone $expectedHtml;
        if (is_object($actualHtml))
            $actualHtml = clone $actualHtml;

        $expectedElement = htmlqp($expectedHtml, 'body')->get(0);
        $actualElement = htmlqp($actualHtml, 'body')->get(0);

        if (is_string($expectedHtml) && is_string($actualHtml)) {
            $expectedHtmlTrimmed = trim($expectedHtml);
            $actualHtmlTrimmed = trim($actualHtml);
            $message = <<<EOT
{$message}
Expected HTML:
{$expectedHtmlTrimmed}

Actual HTML:
{$actualHtmlTrimmed}

EOT;
        }

        $this->assertNotNull($actualHtml);
        $this->assertEqualXMLStructure($expectedElement, $actualElement, true,
            $message);
    }

    public function assertStat($expectedLabel, $expectedValue, $expectedTransformed = null, $expectedWarning = null) {
        if (is_null($this->stats))
            $this->fail('the test class requires a valid PageStatistics object');
        $statsArray = $this->stats->getStats();
        $this->assertArrayHasKey($expectedLabel, $statsArray);
        $stat = $statsArray[$expectedLabel];
        $this->assertEquals($expectedValue, $stat['value']);
        if (!is_null($expectedTransformed))
            $this->assertEquals($expectedTransformed, $stat['transformed']);
        if (!is_null($expectedWarning))
            $this->assertEquals($expectedWarning, $stat['warning']);
    }
}
