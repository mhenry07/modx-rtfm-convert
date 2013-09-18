<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\TextTransformers;


use RtfmConvert\PageData;

/**
 * Class CharsetDeclarationTextTransformer
 * Add a utf-8 charset declaration to force QueryPath to handle utf-8 properly.
 *
 * @package RtfmConvert\TextTransformers
 */
class CharsetDeclarationTextTransformer extends AbstractTextTransformer {

    /**
     * @param string|PageData $input The input string or page data.
     * @return string The transformed string.
     */
    public function transform($input) {
        $html = is_string($input) ? $input : $input->getHtmlDocument();
        if (preg_match('/<meta\b[^>]*\bcharset=/i', $html))
            return $html;
        $metaCharset = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        return preg_replace('/<head\b[^>]*>/i', "$0\n{$metaCharset}", $html, 1);
    }
}
