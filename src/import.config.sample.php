<?php
return array(
    'modx_core_config' => '/path/to/modx/config.core.php',
    'fix_links_for_base_href' => false, // if false, assume it will be handled by a plugin
    'update_confluence_hrefs' => true,
    'use_modx_link_tags' => false, // only applies if update_confluence_hrefs is true

    'source_path' => dirname(__DIR__) . '/data/convert',
    'source_has_html_extensions' => false,
    'toc_dir' => dirname(__DIR__) . '/oldrtfm-toc',

    // note: key is the confluence space key and destContext is the context key
    // this is different from the original
    'spaces_config' => array(
        'revolution20' => array(
            'destContext' => 'revolution',
            'importParent' => 1384
        ),
        'MODx096' => array(
            'destContext' => 'evolution',
            'importParent' => 1385
        ),
        'Evo1' => array(
            'destContext' => 'evolution',
            'importParent' => 1385
        ),
        'XPDO10' => array(
            'destContext' => 'xpdo',
            'importParent' => 1380
        ),
        'xPDO20' => array(
            'destContext' => 'xpdo',
            'importParent' => 1380
        ),
        'ADDON' => array(
            'destContext' => 'extras',
            'importParent' => 4
        ),
        'community' => array(
            'destContext' => 'community',
            'importParent' => 1379
        ),
    )
);
