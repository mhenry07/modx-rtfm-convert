<?php
/**
 * Fix parent for extras imported into MODX
 * Based on FixExtras.php by Jason Coward (opengeek)
 */

namespace RtfmImport;

use modResource;
use modX;


class ExtrasFixer {
    protected $config;
    protected $modx;

    public function __construct(array $config, modX $modx) {
        $this->config = $config;
        $this->modx = $modx;
    }

    public function fix(array $imports) {
        $modx = $this->modx;

        $contextKey = 'extras';
        $fromParentId = 659; // MODX Extras
        $toParentId = 759; // Revolution Extras
        if ($contextKey != $modx->context->key &&
            !$modx->switchContext($contextKey)) {
            echo "ERROR switching to context {$contextKey} to fix extras\n";
        }

        /** @var modResource $resource */
        foreach ($modx->getIterator('modResource', array('context_key' => $contextKey, 'parent' => $fromParentId)) as $resource) {
            $resource->set('parent', $toParentId);
            if (!$resource->save()) {
                echo "An error occurred updating parent for {$resource->get('pagetitle')}\n";
                continue;
            }
            echo "Updated parent for {$resource->get('pagetitle')} [{$toParentId}]\n";

            DocOrganizer::updateImportDestHref($imports, $resource);
        }

        return $imports;
    }
}
