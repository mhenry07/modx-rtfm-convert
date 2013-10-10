<?php
/**
 * Import converted docs into MODX
 * Based on ImportSpaceContent.php by Jason Coward (opengeek)
 */

namespace RtfmImport;

use modX;
use RtfmConvert\OldRtfmTocParser;
use RtfmConvert\PathHelper;
use RtfmConvert\RtfmQueryPath;

class DocImporter {
    protected $config;
    protected $modx;

    public function __construct(array $config, modX $modx) {
        $this->config = $config;
        $this->modx = $modx;
    }

    public function import() {
        echo "*** Importing {$this->config['source_path']} into MODX ***\n";

        $modx = $this->modx;

        $nomatches = array();
        $imports = array();

        $pageIdTV = $modx->getValue($modx->newQuery('modTemplateVar', array('name' => 'pageId'))->prepare());

        $tocParser = new OldRtfmTocParser();
        $hrefs = $tocParser->parseTocDirectory($this->config['toc_dir']);
        foreach ($hrefs as $href) {
            $import = array(
                'source_href' => $href,
                'status' => 'unknown'
            );
            $filename = PathHelper::getConversionFilename($href,
                $this->config['source_path'],
                $this->config['source_has_html_extensions']);
            $pageName = basename($filename);
            if ($this->config['source_has_html_extensions'])
                $pageName = basename($filename, '.html');
            $fileContent = file_get_contents($filename);

            $qp = RtfmQueryPath::htmlqp($fileContent);
            $body = $qp->top('body');
            /** @var string $space */
            $space = $body->attr('data-source-space-key');

            if (!array_key_exists($space, $this->config['spaces_config'])) {
                echo "ERROR looking up config for space {$space}\n";
                $import['status'] = 'error';
                $imports[] = $import;
                continue;
            }
            $spaceConfig = $this->config['spaces_config'][$space];
            $contextKey = $spaceConfig['destContext'];
            $import['dest_context'] = $contextKey;

            if (!$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to import {$space}\n";
                $import['status'] = 'error';
                $imports[] = $import;
                continue;
            }

//            echo "\n\n*** Importing {$space} into context {$contextKey} ***\n";
//
//            if (
//                in_array(basename($filename), array('.', '..', /*'Home', */'index.html'))
//                || strpos(basename($filename), '.') === 0
//                || strpos(basename($filename), '?') !== false
//                || is_dir($filename)
//            ) {
//                continue;
//            }

            $pageTitle = str_replace('+', ' ', $pageName);
            if ($qp->top('title')->count() > 0)
                $pageTitle = trim($qp->top('title')->text(), " \n\r\t");

            /** @var string $sourcePageId */
            $sourcePageId = '';
            if ($body->hasAttr('data-source-page-id'))
                $sourcePageId = $body->attr('data-source-page-id');

            /** @var string $sourceParentPageId */
            $sourceParentPageId = '';
            if ($body->hasAttr('data-source-parent-page-id'))
                $sourceParentPageId = $body->attr('data-source-parent-page-id');

            $matches = array();
            // note: using regex instead of QueryPath to preserve certain text transfomations
            if (preg_match('#<body[^>]*>(.*)</body>#sm', $fileContent, $matches) !== 1) {
//                $nomatches[$pageName] = $fileContent;
                $nomatches[] = "[{$contextKey}] {$pageName}";
                $import['status'] = 'skipped';
                $imports[] = $import;
                continue;
            }
            $pageContent = trim($matches[1], " \n\r\t");

            /** @var \modResource $document */
            $query = $modx->newQuery('modResource', array('context_key' => $modx->context->get('key')));
            $query->innerJoin('modTemplateVarResource', 'tv', array('tv.tmplvarid' => $pageIdTV, 'tv.value' => $sourcePageId, 'tv.contentid = modResource.id'));
            $document = $modx->getObject('modResource', $query);
            if ($document) {
                if ('modDocument' !== $document->get('class_key')) {
                    echo "Skipping import of content for pageId {$sourcePageId}; Resource converted to {$document->get('class_key')}\n";
                    $import['status'] = 'skipped';
                    $imports[] = $import;
                    continue;
                }
                if ('Home' === $document->get('pagetitle')) {
                    echo "Skipping import of existing Home page with pageId {$sourcePageId}\n";
                    $import['status'] = 'skipped';
                    $imports[] = $import;
                    continue;
                }
                echo "Re-importing {$pageName} with title {$pageTitle} and pageId {$sourcePageId}\n";

                $import['status'] = 'updated';
                $destLink = $qp->top('link[title="dest"]');
                if ($destLink->count() > 0) {
                    $destHref = $destLink->attr('href');
                    $matches = array();
                    if (preg_match('#^http://rtfm\.modx(?:\.com)?(/.+)$#', $destHref, $matches) === 1) {
                        $import['dest_href'] = $matches[1];
                    }
                }
            }
            if (!$document) {
                $parentId = $spaceConfig['importParent'];
                $query = $modx->newQuery('modResource');
                $query->innerJoin('modTemplateVarResource', 'tv',
                    array('tv.tmplvarid' => $pageIdTV, 'tv.value' => $sourceParentPageId, 'tv.contentid = modResource.id'));
                $parentDoc = $modx->getObject('modResource', $query);
                if ($parentDoc)
                    $parentId = $parentDoc->get('id');

                $document = $modx->newObject(
                    'modDocument',
                    array(
                        'parent' => $parentId,
                        'context_key' => $modx->context->get('key'),
                        'pagetitle' => $pageTitle,
                        'alias' => $pageTitle,
                        'published' => true,
                        'template' => 1,
                    )
                );
                echo "Importing {$pageName} with title {$pageTitle} and pageId {$sourcePageId}\n";
                $import['status'] = 'imported';
            }
            if (empty($pageContent)) {
                echo "Skipping import of pageId {$sourcePageId} -- Empty content\n";
                $nomatches[] = "[{$contextKey}] {$pageName}";
                $import['status'] = 'skipped';
                $imports[] = $import;
                continue;
            }

            $document->setContent($pageContent);
            if (!$document->save()) {
                echo "An error occurred importing {$pageName}\n";
                $import['status'] = 'error';
                $imports[] = $import;
                continue;
            }

            $import['dest_id'] = $document->get('id');
            $imports[] = $import;

            if (!$document->setTVValue('pageId', $sourcePageId)) {
                echo "An error occurred saving pageId {$sourcePageId} for {$pageName}\n";
            }
            if (!$document->setTVValue('parentPageId', $sourceParentPageId)) {
                echo "An error occurred saving parentPageId {$sourceParentPageId} for {$pageName}\n";
            }
        }
        if (!empty($nomatches)) echo "Could not import:\n" . print_r($nomatches, true);
        return $imports;
    }
}
