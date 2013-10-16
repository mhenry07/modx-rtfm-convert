<?php

$sourceRoot = 'old/';
$destRoot = 'rtfm.modx.com/';
$spaceFolders = array('ADDON', 'community', 'Evo1', 'examples', 'MODx096', 'revolution20', 'XPDO10', 'xPDO20');
$pageIdFolder = 'pageId';

foreach ($spaceFolders as $spaceFolder) {
    $spaceDir = $sourceRoot . $spaceFolder;
    foreach (glob($spaceDir . '/*/original.new.html') as $filename) {
        $pageName = basename(dirname($filename));
        $dest = $destRoot . 'display/' . $spaceFolder . '/' . $pageName . '.html';
        if (copy($filename, $dest)) {
            echo "Copied {$filename} to {$dest}\n";
        } else {
            echo "ERROR copying {$filename} to {$dest}\n";
        }
    }
}

// TODO: organize pageIds
$pageIdDir = $sourceRoot . $pageIdFolder;
foreach (glob($pageIdDir . '/*/original.new.html') as $filename) {
    $pageId = basename(dirname($filename));
    $dest = $destRoot . 'pages/action.viewpage/pageId=' . $pageId . '.html';
    if (copy($filename, $dest)) {
        echo "Copied {$filename} to {$dest}\n";
    } else {
        echo "ERROR copying {$filename} to {$dest}\n";
    }
}
