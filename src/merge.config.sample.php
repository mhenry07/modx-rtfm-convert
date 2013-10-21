<?php

return array(
    // assumes this file is located in:
    // {modx_root}/core/components/rtfmmerge/model/rtfm-convert/src
    'modx_core_config' => '/path/to/modx/config.core.php',

    // if false, assume it will be handled by a plugin
    'fix_links_for_base_href' => false,

    'update_confluence_hrefs' => true,
    'use_modx_link_tags' => false, // only applies if update_confluence_hrefs is true

    'normalize_links' => true,

    'source_files' => dirname(__FILE__) . '/merge.files.php',
    'source_path' => dirname(__DIR__) . '/data/merge',
    'source_has_html_extensions' => true,

    // note: key is the confluence space key and destContext is the modx context key
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
