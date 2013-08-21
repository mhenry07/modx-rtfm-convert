<?php

require '../vendor/autoload.php';

$path = '/display/revolution20/Tag+Syntax';
//$tocFile = 'community.html';
$oldBaseUrl = 'http://oldrtfm.modx.com';
$newBaseUrl = 'http://rtfm.modx.com';

function stripCarriageReturns($str) {
    return str_replace(chr(13), '', $str);
}

// get all hrefs from a file
function getHrefs($filename) {
    $hrefs = array();
    $html = stripCarriageReturns(file_get_contents($filename));

    $doc = htmlqp($html);
    foreach ($doc->find('a') as $link)
        $hrefs[] = $link->attr('href');
    return $hrefs;
}

function getSubstringBetween($str, $startStr, $endStr) {
    $startPos = strpos($str, $startStr);
    if ($startPos === false)
        return false;
    $startPos += strlen($startStr);

    $endPos = strpos($str, $endStr, $startPos);
    if ($endPos === false)
        return false;

    $len = $endPos - $startPos;
    return substr($str, $startPos, $endPos);
}

function getWebPage($baseUrl, $path) {
    $url = $baseUrl . $path;
    $html = file_get_contents($url);
    if ($html === false)
        exit('Error retrieving {$url}');
    return stripCarriageReturns($html);
}

function getNewRtfmContent($html) {
    $newContentStart = '<!-- start content -->';
    $newContentEnd = '<!-- end content -->';

    $content = getSubstringBetween($html, $newContentStart, $newContentEnd);
    if ($content === false)
        exit('Error extracting content');
    return $content;
}

function getOldRtfmContent($html) {
    $qp = htmlqp($html, 'div.wiki-content');
    return $qp->html();
}

function getContent($html, $newOrOld) {
    if ($newOrOld == 'new') 
        return getNewRtfmContent($html);
    return getOldRtfmContent($html);
}

function tidyHtml($html) {
    $config = array(
        'output-xhtml' => true,
        'show-body-only' => true,
        'break-before-br' => true,
        'indent' => false,
        'vertical-space' => true,
        'wrap' => 0,
        'char-encoding' => 'utf8',
        'newline' => 'LF',
        'output-bom' => false);
    $tidy = new tidy();
    return $tidy->repairString($html, $config, 'utf8');
}

function getTextContent($html) {
    return htmlqp($html)->text();
}

function getRtfmText($baseUrl, $path, $newOrOld) {
    print 'Retrieving {$baseUrl}{$path}' . PHP_EOF;
    $fullHtml = getWebPage($baseUrl, $path);
    $content = getContent($fullHtml, $newOrOld);
    $tidy = tidyHtml($content);
    return getTextContent($tidy);
}

function diff($path) {
    $oldText = getRtfmText($GLOBALS['oldBaseUrl'], $path, 'old');
    $newText = getRtfmText($GLOBALS['newBaseUrl'], $path, 'new');
    return xdiff_string_diff($oldText, $newText);
}

print diff($path);
