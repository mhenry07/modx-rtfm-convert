<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;
use tidy;

// TODO: allow more flexible configuration of Html Tidy
class HtmlTidyTextTransformer extends AbstractTextTransformer {

    /**
     * @param string|PageData $input The input string or page data.
     * @return string The transformed string.
     */
    public function transform($input) {
        $html = '';
        if (is_string($input))
            $html = $input;
        if (is_object($input))
            $html = $input->getHtmlDocument();
        $config = array(
            'doctype' => 'omit',
            'output-xhtml' => true,
            'break-before-br' => true,
            'vertical-space' => true,
            'wrap' => 0,
            'char-encoding' => 'utf8',
            'newline' => 'LF',
            'output-bom' => false,
            'tidy-mark' => false);
        $tidy = new tidy();
        $tidied = $tidy->repairString($html, $config, 'utf8');
        $tidied = $this->removeXmlNs($tidied);
        $tidied = $this->insertHtml5Doctype($tidied);
        return $tidied;
    }

    protected function insertHtml5Doctype($html) {
        if (strpos($html, '<html') === 0)
            return "<!DOCTYPE html>\n{$html}";
        return $html;
    }

    protected function removeXmlNs($html) {
        $pattern = '/(<html\b[^<>]*) xmlns="[^"<>]*"/';
        return preg_replace($pattern, '$1', $html);
    }
}
