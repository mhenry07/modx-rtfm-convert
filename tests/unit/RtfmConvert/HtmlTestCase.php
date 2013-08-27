<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class HtmlTestCase extends \PHPUnit_Framework_TestCase {
    /**
     * @see \PHPUnit_Framework_Assert::assertTag()
     * @param $expectedHtml
     * @param $actualHtml
     * @param string $message
     */
    protected function assertHtmlStringEquals($expectedHtml, $actualHtml, $message = '') {
        $expectedElement = htmlqp($expectedHtml, 'body')->get(0);
        $actualElement = htmlqp($actualHtml, 'body')->get(0);

        $expectedHtmlTrimmed = trim($expectedHtml);
        $actualHtmlTrimmed = trim($actualHtml);
        $formattedMessage = <<<EOT
{$message}
Expected HTML:
{$expectedHtmlTrimmed}

Actual HTML:
{$actualHtmlTrimmed}

EOT;

        $this->assertEqualXMLStructure($expectedElement, $actualElement, true,
            $formattedMessage);
    }
}
