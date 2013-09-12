<?php

// note: uncomment extension=php_tidy.dll in php.ini
// should probably use indent => true and default wrap for posting to new rtfm

$src = '../../data/dest.html';
$dest = '../../data/tidy.html';

// see http://tidy.sourceforge.net/docs/quickref.html
$config = array(
    //'drop-font-tags' => true, // handled by main.php
    'output-xhtml' => true,
    'show-body-only' => true,
    'break-before-br' => true,
    'indent' => false,
    'indent-spaces' => 4,
    'tab-size' => 4,
    'vertical-space' => true,
    'wrap' => 0,
    'char-encoding' => 'utf8',
    'newline' => 'LF',
    'output-bom' => false);

$tidy = new tidy();
$out = $tidy->repairFile($src, $config, 'utf8');

file_put_contents($dest, $out);
print $out;
