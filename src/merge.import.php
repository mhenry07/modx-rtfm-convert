<?php
/**
 * @author: Mike Henry
 *
 * Merge import (step 10): re-import merged changes
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmImport\ContentFixer;
use RtfmImport\DocImporter;

$config = include dirname(__FILE__) . '/merge.config.php';
require_once $config['modx_core_config'];
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$files = include $config['source_files'];

function setOptionsFromUrl(array &$config, array $options) {
    foreach ($options as $option)
        if (isset($_GET[$option]))
            $config[$option] = (boolean)$_GET[$option];
}

function getPages(array $files, array $config) {
    $pages = array();
    foreach ($files as $filename) {
        $pages[] = array(
            'filename' => $config['source_path'] . $filename,
            'href' => getConversionPath($filename)
        );
    }
    return $pages;
}

function getConversionPath($filename) {
    preg_match('#^(.*)\.html$#', $filename, $matches);
    return $matches[1];
}

header("Content-Type: text/plain");

setOptionsFromUrl($config, array(
    'fix_links_for_base_href',
    'normalize_links',
    'update_confluence_hrefs',
    'use_modx_link_tags'
));

$modx = modX::getInstance();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$importer = new DocImporter($config, $modx);
$contentFixer = new ContentFixer($config, $modx);

$pages = getPages($files, $config);
$imported = $importer->import($pages);
$imported = $contentFixer->fix($imported);
