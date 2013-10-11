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
                    self::updateImportDestHref($imports, $resource);
                    $this->reorganizeChildren($imports, $spaceConfig, $resource);
                } else {
                    if ((integer)$parentId == $resource->get('id')) {
                        echo "Attempt to set parent to self for {$resource->get('pagetitle')} using parentPageId {$parentPageId}\n";
                    } else {
                        echo "Could not find parent for {$resource->get('pagetitle')} using parentPageId {$parentPageId}\n";
                    }
                }
            }
        }
        return $imports;
    }

    // update parent for pages whose parentPageId tv value equals confluence pageId
    protected function reorganizeChildren(array &$imports, array $spaceConfig,
                                          modResource $parentResource) {
        $modx = $this->modx;
        $parentPageIdTV = $modx->getObject('modTemplateVar',
            array('name' => 'parentPageId'));
        $parentId = $parentResource->get('id');
        $parentPageId = $parentResource->getTVValue('pageId');

        $query = $modx->newQuery('modResource',
            array('context_key' => $parentResource->get('context_key')));
        $query->innerJoin('modTemplateVarResource', 'tv',
            array(
                'tv.tmplvarid' => $parentPageIdTV->get('id'),
                'tv.value' => $parentPageId,
                'tv.contentid = modResource.id'));
        /** @var modResource $childResource */
        foreach ($modx->getIterator('modResource', $query) as $childResource) {
            if ($childResource->get('parent') == $spaceConfig['importParent'] ||
                $childResource->get('parent') == $parentId)
                continue;
            if ($childResource->get('id') == $parentId) {
                echo "Attempt to set parent to self for {$childResource->get('pagetitle')} for parentPageId {$parentPageId}\n";
                continue;
            }
            $childResource->set('parent', $parentId);
            if (!$childResource->save()) {
                echo "An error occurred updating parent for {$childResource->get('pagetitle')}\n";
                continue;
            }
            echo "Updated parent for {$childResource->get('pagetitle')} [{$parentId}:{$parentPageId}]\n";
            self::updateImportDestHref($imports, $childResource);
        }
    }

    public static function updateImportDestHref(array &$imports,
                                                modResource $resource) {
        $id = $resource->get('id');
        foreach ($imports as $index => $import) {
            if ($import['dest_id'] == $id) {
                $imports[$index]['dest_href'] =
                    "/{$resource->get('context_key')}/{$resource->get('uri')}";
                return;
            }
        }
    }
}
