<?php
require dirname(__DIR__) . '/vendor/autoload.php';

use RtfmImport\SpaceImporter;

$config = include dirname(__FILE__) . '/import.config.php';
include $config['modx_core_config'];
include MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx = modX::getInstance();
$modx->initialize('mgr');
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');

$importer = new SpaceImporter($config, $modx);
$importer->import();
