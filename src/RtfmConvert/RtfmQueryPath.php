<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;
use QueryPath\DOMQuery;
use RtfmConvert\TextTransformers\CrlfToLfTextTransformer;


/**
 * Class RtfmQueryPath
 * @package RtfmConvert
 *
 * A helper class for QueryPath
 */
class RtfmQueryPath {
    /**
     * A wrapper for QueryPath htmlqp().
     * @see htmlqp()
     *
     * @param string|\DOMDocument|\SimpleXMLElement|\DOMNode|\DOMNode[]|\QueryPath\DOMQuery $document
     *  A document in one of the forms listed above.
     *  Note: if it's a string, assume it's a whole document, or a body fragment
     *  or child of body.
     *  UTF-8 is assumed.
     * @param string $selector
     *  A CSS 3 selector.
     * @param array $options
     *  An associative array of options. See qp() for supported options.
     * @return \QueryPath\DOMQuery
     */
    public static function htmlqp($document = null, $selector = null,
                                     $options = []) {
        if (is_string($document)) {
            // remove carriage returns to prevent output of &#13; entities
            $eolTransformer = new CrlfToLfTextTransformer();
            $document = $eolTransformer->transform($document);

            // wrap fragment in HTML5 template with utf-8 charset to avoid utf-8 issues
            if (preg_match('/^\s*\<(?:!DOCTYPE|html)\b/i', $document) !== 1) {
                $template = '<!DOCTYPE html><html><head><meta charset="utf-8"><title></title></head>%s</html>';
                if (preg_match('/^\s*\<body\b/i', $document) !== 1)
                    $template = sprintf($template, '<body>%s</body>');
                $document = sprintf($template, $document);
            }
        }
        $defaultOptions = array(
            'convert_to_encoding' => 'utf-8',
            'omit_xml_declaration' => true
        );
        $options = array_merge($defaultOptions, $options);
        return htmlqp($document, $selector, $options);
    }

    // TODO: add a selector
    // note: DOMQuery::xhtml() may add undesired spaces between adjacent inline
    // elements in some cases
    public static function getHtmlString(DOMQuery $qp) {
        $html = '';
        /** @var DOMQuery $match */
        foreach ($qp as $match)
            $html .= $qp->document()->saveHTML($match->get(0));
        return trim($html);
    }

    // count all descendant elements of the current match
    // (not including the selected element)
    public static function countAll(DOMQuery $qp, $includeSelf = false) {
        $count = $qp->find('*')->count();
        if ($includeSelf)
            $count += $qp->count();
        return $count;
    }
}
