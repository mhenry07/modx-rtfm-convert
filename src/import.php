<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use RtfmImport\ContentFixer;
use RtfmImport\DocImporter;
use RtfmImport\DocOrganizer;
use RtfmImport\ExtrasFixer;
use RtfmImport\Modx09xAliasFixer;
use RtfmImport\ResourceGroupSetter;

$config = include dirname(__FILE__) . '/import.config.php';
require_once $config['modx_core_config'];
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = modX::getInstance();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$modx09xFixer = new Modx09xAliasFixer($config, $modx);
$importer = new DocImporter($config, $modx);
$organizer = new DocOrganizer($config, $modx);
$extrasFixer = new ExtrasFixer($config, $modx);
$contentFixer = new ContentFixer($config, $modx);
$groupSetter = new ResourceGroupSetter($modx);

$modx09xFixer->fix();
$imported = $importer->import();
$imported = $organizer->organize($imported);
$imported = $extrasFixer->fix($imported);
$imported = $contentFixer->fix($imported);
$imported = $groupSetter->set($imported);
