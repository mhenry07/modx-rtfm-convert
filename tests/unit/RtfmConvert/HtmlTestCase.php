<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use DOMElement;
use PHPUnit_Util_XML;
use QueryPath\DOMQuery;
use tidy;

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
     * Note that assertEqualXMLStructure does not compare attribute values or
     * text nodes. Neither does assertEqual on a DOMElement.
     */
    protected function assertHtmlEquals($expectedHtml, $actualHtml, $message = '') {
        if (is_null($actualHtml) || $actualHtml === '')
            $this->fail("{$message}\nActual HTML cannot be empty");

        // prevent objects from being altered
        if (is_object($expectedHtml))
            $expectedHtml = clone $expectedHtml;
        if (is_object($actualHtml))
            $actualHtml = clone $actualHtml;

        $expectedQp = htmlqp($expectedHtml, 'body');
        $actualQp = htmlqp($actualHtml, 'body');

        $this->assertEquals($this->normalizeHtml($expectedQp),
            $this->normalizeHtml($actualQp), $message);
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

    protected function normalizeHtml(DOMQuery $qp) {
        $html = $qp->document()->saveHTML($qp->get(0));
        $config = array(
            'output-html' => true,
            'show-body-only' => true,
            'break-before-br' => true,
            'indent' => true,
            'indent-spaces' => 2,
            'vertical-space' => true,
            'wrap' => 0,
            'char-encoding' => 'utf8',
            'newline' => 'LF',
            'output-bom' => false,
            'tidy-mark' => false);
        $tidy = new tidy();
        return $tidy->repairString($html, $config, 'utf8');

    }
}
