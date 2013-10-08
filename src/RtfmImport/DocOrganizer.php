<?php
/**
 * Organize newly imported docs
 * Based on OrganizeSpace.php by Jason Coward (opengeek)
 */

namespace RtfmImport;


use modX;

class DocOrganizer {
    protected $modx;

    public function __construct(modX $modx) {
        $this->modx = $modx;
    }

    public function organize(array $imports) {
        $modx = $this->modx;

        foreach ($imports as $source_href => $import) {
            if ($import['status'] !== 'imported')
                continue;

            $contextKey = $import['dest_context'];
            if (!$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to organize imported resource\n";
                continue;
            }

            /** @var \modDocument $document */
            $document = $modx->getObject('modResource', array('id' => $import['dest_id']));
            $pageIdTV = $modx->getObject('modTemplateVar', array('name' => 'pageId'));
            $parentPageId = $document->getTVValue('parentPageId');

            echo "Organizing document {$document->get('pagetitle')}\n";

            if (!$parentPageId) {
                echo "Could not find parentPageId for {$document->get('pagetitle')}\n";
                continue;
            }

            $pageIdQuery = $modx->newQuery('modTemplateVarResource',
                array('tmplvarid' => $pageIdTV->get('id'), 'value' => $parentPageId))->select('contentid');
            $parentId = $modx->getValue($pageIdQuery->prepare());
            if ($parentId && (integer)$parentId != $document->get('id')) {
                $document->set('parent', $parentId);
                if (!$document->save()) {
                    echo "An error occurred updating parent for {$document->get('pagetitle')}\n";
                    continue;
                }
                echo "Updated parent for {$document->get('pagetitle')} [{$parentId}:{$parentPageId}]\n";
            } else {
                if ((integer)$parentId != $document->get('id')) {
                    echo "Attempt to set parent to self for {$document->get('pagetitle')} using parentPageId {$parentPageId}\n";
                } else {
                    echo "Could not find parent for {$document->get('pagetitle')} using parentPageId {$parentPageId}\n";
                }
            }
            $imports[$source_href]['dest_href'] = "/{$contextKey}/{$document->get('uri')}";
        }
        return $imports;
    }
}
