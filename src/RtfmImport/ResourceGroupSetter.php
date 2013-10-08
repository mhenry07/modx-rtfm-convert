<?php
/**
 * Add resources to appropriate resource groups
 * Based on SetResourceGroups.php by Jason Coward (opengeek)
 */

namespace RtfmImport;


use modX;

class ResourceGroupSetter {
    protected $modx;

    public function __construct(modX $modx) {
        $this->modx = $modx;
    }

    public function set(array $imports) {
        echo "\n\n*** Adding Resources to appropriate Resource Groups ***\n";

        $modx = $this->modx;

        foreach ($imports as $source_href => $import) {
            if ($import['status'] !== 'imported' && $import['status'] !== 'updated')
                continue;

            $contextKey = $import['dest_context'];
            if (!$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to assign Resource Group\n";
                continue;
            }

            /** @var \modResource $resource */
            $resource = $modx->getObject('modResource', array('id' => $import['dest_id']));

            if ($resource->get('parent') > 0) {
                if ($resource->joinGroup($contextKey))
                    echo "Added Resource [{$resource->get('id')}] {$resource->get('pagetitle')} to Resource Group {$contextKey}\n";
            } else {
                if ($resource->joinGroup('AdminOnly'))
                    echo "Added Resource [{$resource->get('id')}] {$resource->get('pagetitle')} to Resource Group AdminOnly\n";
            }
        }
        return $imports;
    }
}
