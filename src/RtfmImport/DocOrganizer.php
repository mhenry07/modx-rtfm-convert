<?php
/**
 * Organize hierarchy of docs in imported containers
 * Based on OrganizeSpace.php by Jason Coward (opengeek)
 */

namespace RtfmImport;


use modResource;
use modX;

class DocOrganizer {
    protected $config;
    protected $modx;

    public function __construct(array $config, modX $modx) {
        $this->config = $config;
        $this->modx = $modx;
    }

    public function organize(array $imports) {
        $modx = $this->modx;

        foreach ($this->config['spaces_config'] as $spaceKey => $spaceConfig) {
            $contextKey = $spaceConfig['destContext'];
            if ($contextKey != $modx->context->key &&
                !$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to organize imported resources\n";
                continue;
            }
            echo "Organizing space {$contextKey}\n";

            /** @var modResource $resource */
            foreach ($modx->getIterator('modResource', array('context_key' => $modx->context->get('key'), 'parent' => $spaceConfig['importParent'])) as $resource) {
                $pageIdTV = $modx->getObject('modTemplateVar', array('name' => 'pageId'));
                $parentPageId = $resource->getTVValue('parentPageId');

                if (!$parentPageId) {
                    echo "Could not find parentPageId for {$resource->get('pagetitle')}\n";
                    continue;
                }

                $pageIdQuery = $modx->newQuery('modTemplateVarResource',
                    array('tmplvarid' => $pageIdTV->get('id'), 'value' => $parentPageId))->select('contentid');
                $parentId = $modx->getValue($pageIdQuery->prepare());
                if ($parentId && (integer)$parentId != $resource->get('id')) {
                    $resource->set('parent', $parentId);
                    if (!$resource->save()) {
                        echo "An error occurred updating parent for {$resource->get('pagetitle')}\n";
                        continue;
                    }
                    echo "Updated parent for {$resource->get('pagetitle')} [{$parentId}:{$parentPageId}]\n";
                } else {
                    if ((integer)$parentId != $resource->get('id')) {
                        echo "Attempt to set parent to self for {$resource->get('pagetitle')} using parentPageId {$parentPageId}\n";
                    } else {
                        echo "Could not find parent for {$resource->get('pagetitle')} using parentPageId {$parentPageId}\n";
                    }
                }
                self::updateImportDestHref($imports, $contextKey, $resource);
            }
        }
        return $imports;
    }

    public static function updateImportDestHref(array &$imports, $contextKey,
                                                modResource $resource) {
        $id = $resource->get('id');
        foreach ($imports as $source_href => $import) {
            if ($import['dest_id'] == $id) {
                $imports[$source_href]['dest_href'] = "/{$contextKey}/{$resource->get('uri')}";
                return;
            }
        }
    }
}
