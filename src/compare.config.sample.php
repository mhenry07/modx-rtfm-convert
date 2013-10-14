<?php
return array(
    'cache_dir' => '/path/to/cache',
    'stats_file_format' => dirname(__DIR__) . '/data/compare-%s-%s-%s.json',
    'text_dir' => dirname(__DIR__) . '/data/text',
    'toc_dir' => dirname(__DIR__) . '/oldrtfm-toc',
    'sites' => array(
        'oldrtfm.modx.com' => array(
            'type' => 'confluence',
            'url' => 'http://oldrtfm.modx.com',
            'build_pagetrees' => true),
        'rtfm.modx.com' => array(
            'type' => 'modx',
            'url' => 'http://rtfm.modx.com',
            'exclude_child_pages_section' => true),
        'rtfm.modx' => array(
            'type' => 'modx',
            'url' => 'http://rtfm.modx',
            'exclude_child_pages_section' => true)
    )
);
