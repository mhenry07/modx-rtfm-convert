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
     * Warning: changing convert_to_encoding option to utf-8 seems to cause
     * non-breaking spaces to be output incorrectly, like Â&nbsp;
     *
     * @param string|\DOMDocument|\SimpleXMLElement|\DOMNode|\DOMNode[]|\QueryPath\DOMQuery $document
     *  A document in one of the forms listed above.
     * @param string $selector
     *  A CSS 3 selector.
     * @param array $options
     *  An associative array of options. See qp() for supported options.
     * @return \QueryPath\DOMQuery
     */
    public static function htmlqp($document = null, $selector = null,
                                     $options = []) {
        // remove carriage returns to prevent output of &#13; entities
        if (is_string($document)) {
            $eolTransformer = new CrlfToLfTextTransformer();
            $document = $eolTransformer->transform($document);
        }
        $defaultOptions = array('omit_xml_declaration' => true);
        $options = array_merge($defaultOptions, $options);
        return htmlqp($document, $selector, $options);
    }

    // TODO: add a selector
    public static function getHtmlString(DOMQuery $qp) {
        return trim($qp->xhtml());
    }

    // count all descendant elements of the current match
    // (not including the selected element)
    public static function countAll(DOMQuery $qp) {
        return $qp->find('*')->count();
    }
}
