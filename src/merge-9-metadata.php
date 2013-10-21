<?php
/**
 * @author: Mike Henry
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

function getConversionPath($filename) {
    preg_match('#^(.*)\.html$#', $filename, $matches);
    return $matches[1];
}

function getConversionValue(array $convertStats, $key) {
    return $convertStats[$key]['value'];
}

function getTitle(array $convertStats, array $config) {
    $titleKey = $config['metadata_config']['title'];
    return getConversionValue($convertStats, $titleKey);
}

function getLinks(array $convertStats, array $config) {
    $linksConfig = $config['metadata_config']['links'];
    $links = array();
    foreach ($linksConfig as $title => $linkConfig)
        $links[] = array(
            'rel' => $linkConfig['rel'],
            'title' => $title,
            'href' => getConversionValue($convertStats, $linkConfig['key'])
        );
    return $links;
}

function formatLinks(array $links) {
    $output = '';
    foreach ($links as $link) {
        $linkAttributes = formatAttributes($link);
        $output .= "<link{$linkAttributes} />\n";
    }
    return trim($output);
}

function getBodyAttributes(array $convertStats, array $config) {
    $bodyConfig = $config['metadata_config']['body'];
    $bodyAttributes = array();
    foreach ($bodyConfig as $attribute => $key)
        $bodyAttributes[$attribute] = getConversionValue($convertStats, $key);
    return $bodyAttributes;
}

function formatAttributes(array $attributes) {
    $output = '';
    foreach ($attributes as $name => $value) {
        $safeValue = htmlspecialchars($value);
        $output .= " {$name}=\"{$safeValue}\"";
    }
    return $output;
}


$config = array(
    'base_dir' => 'C:/temp/rtfm-changes',
    'conversion_data' => 'C:/Users/mhenry/Documents/Development/modx/import/logs-n-stats/stats-20131011T1450.json',
    'source_files' => dirname(__FILE__) . '/merge.files.php',
    'metadata_config' => array(
        'title' => 'source: pageTitle',
        'links' => array(
            'dest' => array('key' => 'dest: url', 'rel' => 'canonical'),
            'source' => array('key' => 'source: url', 'rel' => 'alternate'),
        ),
        'body' => array(
            'data-dest-page-id' => 'dest: page-id',
            'data-source-page-id' => 'source: pageId',
            'data-source-parent-page-id' => 'source: parentPageId',
            'data-source-space-key' => 'source: spaceKey'
        )
    )
);

$files = include $config['source_files'];

$conversionData = json_decode(file_get_contents($config['conversion_data']), true);

foreach ($files as $filename) {
    echo $filename . PHP_EOL;
    $path = getConversionPath($filename);
    $convert = $conversionData[$path];

    $title = htmlspecialchars(getTitle($convert, $config));
    $linkTags = formatLinks(getLinks($convert, $config));
    $bodyAttributes = formatAttributes(getBodyAttributes($convert, $config));
    $content = trim(file_get_contents($config['base_dir'] . $filename));

    $html = <<<EOT
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8" />
<title>{$title}</title>
{$linkTags}
</head>
<body{$bodyAttributes}>
{$content}
</body>
</html>
EOT;

    file_put_contents($config['base_dir'] . $filename, $html);
}
