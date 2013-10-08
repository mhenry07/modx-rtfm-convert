<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use RtfmImport\ContentFixer;
use RtfmImport\DocImporter;
use RtfmImport\DocOrganizer;
use RtfmImport\ResourceGroupSetter;

$config = include dirname(__FILE__) . '/import.config.php';
include $config['modx_core_config'];
include MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = modX::getInstance();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$importer = new DocImporter($config, $modx);
$organizer = new DocOrganizer($config, $modx);
$fixer = new ContentFixer($config, $modx);
$groupSetter = new ResourceGroupSetter($modx);

$imported = $importer->import();
$imported = $organizer->organize($imported);
$imported = $fixer->fix($imported);
$imported = $groupSetter->set($imported);
