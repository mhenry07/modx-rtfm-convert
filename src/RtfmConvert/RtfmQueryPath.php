<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


/**
 * Class RtfmQueryPath
 * @package RtfmConvert
 *
 * A helper class for QueryPath
 */
class RtfmQueryPath {
    /**
     * A wrapper for QueryPath htmlqp() that changes the default
     * convert_to_encoding option to utf-8.
     * @see htmlqp()
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
        if (!array_key_exists('convert_to_encoding', $options))
            $options['convert_to_encoding'] = 'utf-8';
        return htmlqp($document, $selector, $options);
    }
}
