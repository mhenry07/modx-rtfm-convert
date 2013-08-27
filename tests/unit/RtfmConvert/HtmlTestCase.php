<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class HtmlTestCase extends \PHPUnit_Framework_TestCase {
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
     */
    protected function assertHtmlEquals($expectedHtml, $actualHtml, $message = '') {
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

        $this->assertEqualXMLStructure($expectedElement, $actualElement, true,
            $message);
    }
}
