<?php
/**
 * Fix alias for MODx 0.9.x page (1652) to work with the
 * SubfolderContextMapRouter plugin
 */

namespace RtfmImport;


use modX;

class Modx09xAliasFixer {
    protected $config;
    protected $modx;

    public function __construct(array $config, modX $modx) {
        $this->config = $config;
        $this->modx = $modx;
    }

    public function fix() {
        $modx = $this->modx;

        echo "\n\n*** Fixing MODx 0.9.x alias ***\n";

        $contextKey = 'evolution';
        $pageId = 1652;
        $newAlias = '0.9.x';
        if ($contextKey != $modx->context->key &&
            !$modx->switchContext($contextKey)) {
            echo "ERROR switching to context {$contextKey} to fix MODx 0.9.x alias\n";
        }

        /** @var \modResource $document */
        $document = $modx->getObject('modResource', $pageId);
        if (!$document) {
            echo "ERROR loading page {$pageId} to fix alias\n";
            return;
        }

        $document->set('alias', $newAlias);
        if (!$document->save()) {
            echo "ERROR setting alias for {$document->get('pagetitle')}\n";
            return;
        }

        echo "Updated {$document->get('pagetitle')} alias to {$newAlias}\n";
    }
}
