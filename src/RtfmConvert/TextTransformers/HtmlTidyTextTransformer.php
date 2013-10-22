<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;
use tidy;

class HtmlTidyTextTransformer extends AbstractTextTransformer {
    protected $tidyConfig;

    public function __construct(array $tidyConfig = array()) {
        $this->tidyConfig = $tidyConfig;
    }

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
        $defaultConfig = array(
            'doctype' => 'omit',
            'new-blocklevel-tags' => 'article aside audio details figcaption figure footer header hgroup nav section source summary track video',
            'new-empty-tags' => 'command embed keygen source track wbr',
            'new-inline-tags' => 'canvas command datalist embed keygen mark meter output progress time wbr',
            'output-xhtml' => true,
            'break-before-br' => true,
            'vertical-space' => true,
            'wrap' => 0,
            'char-encoding' => 'utf8',
            'newline' => 'LF',
            'output-bom' => false,
            'tidy-mark' => false);
        $config = array_merge($defaultConfig, $this->tidyConfig);
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
