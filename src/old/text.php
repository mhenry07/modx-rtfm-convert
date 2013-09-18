<?php
require '../../vendor/autoload.php';

// should probably use indent => false and wrap => 0 when doing text comparisons
// TODO: trim groups of empty lines to a single empty line
// could use xdiff for creating diffs http://www.php.net/manual/en/book.xdiff.php
// or maybe git diff (--numstat and --dirstat options might be helpful)

$src = '../../data/tidy.html';
$dest = '../../data/tidy.txt';

$options = array(
    'encoding' => 'utf-8',
    'convert_from_encoding' => 'utf-8',
    'convert_to_encoding' => 'utf-8');

$qpDoc = qp($src, null, $options);
file_put_contents($dest, $qpDoc->text());
