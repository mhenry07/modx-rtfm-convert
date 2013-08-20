<?php
require '../vendor/autoload.php';

//$url = 'http://oldrtfm.modx.com/display/revolution20/Tag+Syntax';
//$url = '../data/tag-syntax.html';
$url = 'http://oldrtfm.modx.com/display/revolution20/Server+Requirements';
$options = array(
    'convert_to_encoding' => 'utf-8',
    'convert_from_encoding' => 'utf-8');

$contents = htmlqp($url, 'div.wiki-content', $options)->html();

// for some reason it's generating some &Acirc; entities
$new = qp($contents, 'div.wiki-content')->branch();
$new->remove('style');
$new->remove('div.Scrollbar');
$new->remove('div.plugin_pagetree');

if ($new->firstChild()->is('p > br.atl-forced-newline:only-child'))
    $new->firstChild()->remove();

$new->find('br.atl-forced-newline')->removeClass('atl-forced-newline');

// TODO: test
//$new->find('font')->content()->unwrap();
//$new->find('b')->content()
//    ->wrap('<strong></strong>')->unwrap();

// tables
$new->find('div.table-wrap')->removeClass('table-wrap');
$new->find('table.confluenceTable')->removeClass('confluenceTable');
$new->find('th.confluenceTh')->removeClass('confluenceTh');
$new->find('td.confluenceTd')->removeClass('confluenceTd');

// code panel
$codePanel = $new->find('.code.panel');
$codePanel->find('div.codeHeader')
    ->contents()->wrap('<p></p>');
$codePanel->find('pre.code-java')
    ->addClass('brush: php')
    ->removeClass('code-java');
$codePanel->find('div.codeHeader, div.codeContent')
    ->contents()->unwrap()->unwrap();

// asides
$aside_types = array('danger', 'info', 'note', 'tip', 'warning');
$panelMacro = $new->find('div.panelMacro');
foreach ($aside_types as $type)
    $panelMacro->has(".{$type}Macro")->addClass($type);
$panelMacro->find('td:last')->contents()
    ->unwrap()->unwrap()->unwrap();
$panelMacro->removeClass('panelMacro');

// TODO: strip <!-- wiki content -->
// TODO: strip &Acirc;
//echo preg_replace('/(&Acirc;)/', '', $newHtml);
// TODO: self-closing tags without unnecessary entities
// TODO: indent/tidy
// TODO: get contents of div.wiki-content

$new->writeHTML();
