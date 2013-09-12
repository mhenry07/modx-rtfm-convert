<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use QueryPath\DOMQuery;
use tidy;

class HtmlTestCase extends \PHPUnit_Framework_TestCase {
    const TRANSFORM = PageStatistics::TRANSFORM;
    const WARNING = PageStatistics::WARNING;

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

    /**
     * @param string $label
     */
    public function assertStatsNotContain($label) {
        $this->assertArrayNotHasKey($label, $this->stats->getStats());
    }

    /*
     * @param string $label
     * @param int $expectedFound
     * @param array $options An associative array of options.
     *  Possible options:
     * * warnings: an int representing the number of warnings
     */
    public function assertValueStat($expectedLabel, $expectedValue,
                                    array $options = array()) {
        if (is_null($this->stats))
            $this->fail('the test class requires a valid PageStatistics object');
        $statsArray = $this->stats->getStats();
        $this->assertArrayHasKey($expectedLabel, $statsArray);
        $stat = $statsArray[$expectedLabel];
        $this->assertEquals($expectedValue, $stat[PageStatistics::VALUE]);

        $expectedWarnings = $this->getOption($options, PageStatistics::WARNING);
        if (!is_null($expectedWarnings)) {
            if ($expectedWarnings == 0) {
                $this->assertArrayNotHasKey(PageStatistics::WARNING, $stat);
            } else {
                $this->assertEquals($expectedWarnings, $stat[PageStatistics::WARNING]);
            }
        }
    }


    /*
     * @param string $label
     * @param int $expectedFound
     * @param array $options An associative array of options.
     *  Possible options:
     * * transformed: an int representing the number of transformations performed
     * * warnings: an int representing the number of warnings
     */
    public function assertTransformStat($label, $expectedFound,
                                        array $options = array()) {
        if (is_null($this->stats))
            $this->fail('the test class requires a valid PageStatistics object');
        $statsArray = $this->stats->getStats();
        $this->assertArrayHasKey($label, $statsArray);
        $stat = $statsArray[$label];
        $this->assertEquals($expectedFound, $stat[PageStatistics::FOUND]);

        $expectedTransformed = $this->getOption($options, PageStatistics::TRANSFORM);
        if (!is_null($expectedTransformed)) {
            if ($expectedTransformed == 0) {
                $this->assertArrayNotHasKey(PageStatistics::TRANSFORM, $stat);
            } else {
                $this->assertEquals($expectedTransformed, $stat[PageStatistics::TRANSFORM]);
            }
        }
        $expectedWarnings = $this->getOption($options, PageStatistics::WARNING);
        if (!is_null($expectedWarnings)) {
            if ($expectedWarnings == 0) {
                $this->assertArrayNotHasKey(PageStatistics::WARNING, $stat);
            } else {
                $this->assertEquals($expectedWarnings, $stat[PageStatistics::WARNING]);
            }
        }
    }

    protected function normalizeHtml(DOMQuery $qp) {
        $html = RtfmQueryPath::getHtmlString($qp);
        $config = array(
            'output-html' => true,
            'show-body-only' => true,
            'break-before-br' => true,
            'indent' => true,
            'indent-spaces' => 2,
            'sort-attributes' => true, // Note: php options differ from docs
            'vertical-space' => true,
            'wrap' => 0,
            'char-encoding' => 'utf8',
            'newline' => 'LF',
            'output-bom' => false,
            'tidy-mark' => false);
        $tidy = new tidy();
        return $tidy->repairString($html, $config, 'utf8');
    }

    protected function getOption(array $options, $key) {
        return array_key_exists($key, $options) ? $options[$key] : null;
    }
}
