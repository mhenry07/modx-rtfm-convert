<?php
/**
 * fix relative links and anchors for base href
 */

$resource = $modx->resource;
$output = &$resource->_output;
$output = str_replace(
    array(
        'href="/',
        'src="/',
        'href="#',
    ),
    array(
        'href="',
        'src="',
        "href=\"{$resource->get('context_key')}/{$resource->get('uri')}#",
    ),
    $output
);
