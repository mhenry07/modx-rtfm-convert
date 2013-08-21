<?php
require '../vendor/autoload.php';

// FIXED: strip &Acirc;
//  * passing qp->html() to create a second qp object seemed to contribute to this
//  * and using html() or innerHTML() versus writeHTML()
// TODO: self-closing tags (br, hr, img, input, meta, etc.)
//  * see http://www.w3.org/TR/html5/syntax.html#void-elements
// TODO: indent/tidy
// TODO: get contents of div.wiki-content
// TODO: warn about certain cases

//$source = 'http://oldrtfm.modx.com/display/revolution20/Tag+Syntax';
$source = '../data/tag-syntax.html';
//$source = 'http://oldrtfm.modx.com/display/revolution20/Server+Requirements';

$options = array(
    'encoding' => 'utf-8',
    'convert_to_encoding' => 'utf-8');

function isComment($index, $item) {
    return $item->nodeType === XML_COMMENT_NODE;
}

// strip carriage returns to prevent &#13; in output
$doc_string = str_replace(chr(13), '', file_get_contents($source));

$doc = htmlqp($doc_string, null, $options);

// strip everything except for title from head
$head = $doc->find('head')->branch();
$title = $head->find('title')->get();
$head->contents()->not($title)->remove();
$head->prepend('<meta charset="utf-8" />');

// strip attributes from body
$doc->find('body')
    ->removeAttr('onload')
    ->removeAttr('id')
    ->removeAttr('class');

$content = $doc->find('div.wiki-content')->branch();

// strip element and comment siblings of .wiki-content
$wikiContent = $content->get();
$content->parent()->contents()->not($wikiContent)
    ->remove();

// remove comments
$content->contents()
    ->filterCallback('isComment')
    ->remove();

// for some reason it's generating some &Acirc; entities
$content->remove('style');
$content->remove('div.Scrollbar');
$content->remove('div.plugin_pagetree');

if ($content->firstChild()->is('p > br.atl-forced-newline:only-child'))
    $content->firstChild()->remove();

$content->find('br.atl-forced-newline')->removeClass('atl-forced-newline');

// TODO: test
//$content->find('font')->contents()->unwrap();

$replaceTags = array(
    'b' => 'strong',
    'i' => 'em',
    'tt' => 'code');
foreach ($replaceTags as $oldTag => $contentTag) {
    $content->find($oldTag)
        ->wrap("<{$contentTag}></{$contentTag}>")
        ->contents()->unwrap();
}

// tables
$content->find('div.table-wrap')->removeClass('table-wrap');
$content->find('table.confluenceTable')->removeClass('confluenceTable');
$content->find('th.confluenceTh')->removeClass('confluenceTh');
$content->find('td.confluenceTd')->removeClass('confluenceTd');

// code panel
$codePanel = $content->find('.code.panel');
$codePanel->find('div.codeHeader')
    ->contents()->wrap('<p></p>');
$codePanel->find('pre.code-java')
    ->addClass('brush: php')
    ->removeClass('code-java');
$codePanel->find('div.codeHeader, div.codeContent')
    ->contents()->unwrap()->unwrap();

// asides
$aside_types = array('danger', 'info', 'note', 'tip', 'warning');
$panelMacro = $content->find('div.panelMacro');
foreach ($aside_types as $type)
    $panelMacro->has(".{$type}Macro")->addClass($type);
$panelMacro->find('td:last')->contents()
    ->unwrap()->unwrap()->unwrap();
$panelMacro->removeClass('panelMacro');


$content->contents()->unwrap();

/*
 * writeHTML() seems to produce the closest output to what we want
 *  but it doesn't close all tags (e.g. <br>)
 *  and it generates a full document
 *
 * writeXHTML() generates harder to read entities
 *  (e.g. &#xA0; instead of &nbsp;)
 *  and uses non self-closing tags (e.g. <br></br>)
 *
 * html() and innerHTML() allow us to get a specific fragment
 *  with self-closing br tags (e.g. <br/>)
 *  but it self-closes anchor tags (<a name=""/>)
 *  and it inserts weird characters
 */
//print $content->top()->find('body')->innerHTML();
$content->writeHTML('../data/dest.html');
