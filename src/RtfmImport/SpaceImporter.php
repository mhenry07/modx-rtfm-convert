<?php
/**
 * Import converted docs into MODX
 * Based on ImportSpaceContent.php by Jason Coward (opengeek)
 */

namespace RtfmImport;

use \modX;
use RtfmConvert\OldRtfmTocParser;
use RtfmConvert\PathHelper;
use RtfmConvert\RtfmQueryPath;

class SpaceImporter {
    protected $config;
    protected $modx;

    public function __construct(array $config, modX $modx) {
        $this->config = $config;
        $this->modx = $modx;
    }

    protected function getContextKey($spaceNeedle) {
        foreach ($this->config['spaces'] as $contextKey => $space) {
            if ($space == $spaceNeedle)
                return $contextKey;
        }
        echo "ERROR looking up context key for space {$spaceNeedle}\n";
        return false;
    }

    public function import() {
        $modx = $this->modx;

        $nomatches = array();

        $pageIdTV = $modx->getValue($modx->newQuery('modTemplateVar', array('name' => 'pageId'))->prepare());

        $tocParser = new OldRtfmTocParser();
        $hrefs = $tocParser->parseTocDirectory($this->config['toc_dir']);
        echo "*** Importing {$this->config['source_path']} into MODX ***\n";
        foreach ($hrefs as $href) {
            $filename = PathHelper::getConversionFilename($href,
                $this->config['source_path'],
                $this->config['source_has_html_extensions']);
            $pageName = basename($filename);
            $fileContent = file_get_contents($filename);

            $qp = RtfmQueryPath::htmlqp($fileContent);
            $body = $qp->top('body');
            $space = $body->attr('data-source-space-key');
            $contextKey = $this->getContextKey($space);

            if (!$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to import {$space}\n";
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

            $sourceParentPageId = '';
            if ($body->hasAttr('data-source-parent-page-id'))
                $sourceParentPageId = $body->attr('data-source-parent-page-id');

            $matches = array();
            // note: using regex instead of QueryPath to preserve certain text transfomations
            if (preg_match('#<body[^>]*>(.*)</body>#sm', $fileContent, $matches)) {
                $pageContent = trim($matches[1], " \n\r\t");

                /** @var \modDocument $document */
                $query = $modx->newQuery('modResource', array('context_key' => $modx->context->get('key')));
                $query->innerJoin('modTemplateVarResource', 'tv', array('tv.tmplvarid' => $pageIdTV, 'tv.value' => $sourcePageId, 'tv.contentid = modResource.id'));
                $document = $modx->getObject('modResource', $query);
                if ($document) {
                    if ('modDocument' !== $document->get('class_key')) {
                        echo "Skipping import of content for pageId {$sourcePageId}; Resource converted to {$document->get('class_key')}\n";
                        continue;
                    }
                    if ('Home' === $document->get('pagetitle')) {
                        echo "Skipping import of existing Home page with pageId {$sourcePageId}\n";
                        continue;
                    }
                    echo "Re-importing {$pageName} with title {$pageTitle} and pageId {$sourcePageId}\n";
                }
                if (!$document) {
                    $document = $modx->newObject(
                        'modDocument',
                        array(
                            'parent' => $this->config[$contextKey]['importParent'],
                            'context_key' => $modx->context->get('key'),
                            'pagetitle' => $pageTitle,
                            'alias' => $pageTitle,
                            'published' => true,
                            'template' => 1,
                        )
                    );
                    echo "Importing {$pageName} with title {$pageTitle} and pageId {$sourcePageId}\n";
                }
                if (empty($pageContent)) {
                    echo "Skipping import of pageId {$sourcePageId} -- Empty content\n";
                    $nomatches[] = "[{$contextKey}] {$pageName}";
                    continue;
                }

                $document->setContent($pageContent);
                if (!$document->save()) {
                    echo "An error occurred importing {$pageName}\n";
                } else {
                    if (!$document->setTVValue('pageId', $sourcePageId)) {
                        echo "An error occurred saving pageId {$sourcePageId} for {$pageName}\n";
                    }
                    if (!$document->setTVValue('parentPageId', $sourceParentPageId)) {
                        echo "An error occurred saving parentPageId {$sourceParentPageId} for {$pageName}\n";
                    }
                }
            } else {
//                $nomatches[$pageName] = $fileContent;
                $nomatches[] = "[{$contextKey}] {$pageName}";
            }
        }
        if (!empty($nomatches)) echo "Could not import:\n" . print_r($nomatches, true);
    }
}
