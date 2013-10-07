<?php
return array(
    'modx_core_config' => '/path/to/modx/config.core.php',
    'source_path' => dirname(__DIR__) . '/data/convert',
    'source_has_html_extensions' => false,
    'toc_dir' => dirname(__DIR__) . '/oldrtfm-toc',
    'spaces' => array(
        'revolution' => array(
            'source' => 'revolution20',
            'importParent' => 1384
        ),
        'evolution' => array(
            'source' => 'MODx096', // ?? not Evo1?
            'importParent' => 1385
        ),
        'xpdo' => array(
            'source' => 'XPDO10', // ?? not XPDO20?
            'importParent' => 1380
        ),
        'extras' => array(
            'source' => 'ADDON',
            'importParent' => 4
        ),
        'community' => array(
            'source' => 'community',
            'importParent' => 1379
        ),
    )
);
