<?php
use RtfmImport\SpaceImporter;

$config = include dirname(__FILE__) . '/import.config.php';

$importer = new SpaceImporter($config);
$importer->import();
