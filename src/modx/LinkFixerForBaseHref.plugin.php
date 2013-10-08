<?php
/**
 * fix relative links and anchors for base href
 *
 * OnWebPagePrerender event
 */

$resource = $modx->resource;
$output = &$resource->_output;

// remove root slash from relative url in href or src attribute
$output = preg_replace('#(<\w+\b[^>]+\b(?:href|src)=[\'"]?)/(?!>)#i', '$1',
    $output);
// prepend anchor with page url
$output = preg_replace('/(<(?:a|area|link)\b[^>]+\bhref=[\'"]?)#/i',
    "$1{$resource->get('context_key')}/{$resource->get('uri')}#", $output);
