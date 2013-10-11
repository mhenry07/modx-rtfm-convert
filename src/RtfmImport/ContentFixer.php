<?php
/**
 * Fix content imported into MODX
 * Based on FixSpaceContent.php by Jason Coward (opengeek)
 */

namespace RtfmImport;

use modDocument;
use modX;


class ContentFixer {
    protected $config;
    protected $modx;

    public function __construct(array $config, modX $modx) {
        $this->config = $config;
        $this->modx = $modx;
    }

    public function fix(array $imports) {
        echo "\n\n*** Fixing content imported into MODX ***\n";

        $modx = $this->modx;

        foreach ($imports as $source_href => $import) {
            if ($import['status'] !== 'imported' && $import['status'] !== 'updated')
                continue;

            $contextKey = $import['dest_context'];
            if ($contextKey != $modx->context->key &&
                !$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to organize imported resource\n";
                continue;
            }

            /** @var modDocument $document */
            $document = $modx->getObject('modDocument', array('id' => $import['dest_id']));
            $pageContent = $document->getContent();

            $count = 0;
            if ($this->config['update_confluence_hrefs'])
                $pageContent = $this->fixRelativeLinks($imports, $contextKey,
                    $document, $pageContent, $count);

            if ($this->config['fix_links_for_base_href'])
                $pageContent = $this->fixLinksForBaseHref($contextKey,
                    $document, $pageContent, $count);

            /* trim it */
            $pageContent = trim($pageContent, " \r\n\t");

            // echo "{$document->get('pagetitle')} [{$count}]:\n\n{$pageContent}\n\n\n~~~===|===~~~\n\n\n";
            // continue;

            $document->setContent($pageContent);
            if (!$document->save()) {
                echo "An error occurred updating {$document->get('pagetitle')}\n";
                continue;
            }
            echo "Fixed {$document->get('pagetitle')} [{$count}]\n";
        }

        return $imports;
    }

    /* fix relative links and anchor links */
    protected function fixRelativeLinks(array $linkMap, $contextKey,
                                        modDocument $document, $pageContent,
                                        &$count) {
        $pageTitle = str_replace(' ', '+', $document->get('pagetitle'));
        $matches = array();
        if (preg_match_all('/(<a\b[^>]+\bhref=")([^#"]+)((?:#[^"]+)?)"/i', $pageContent, $matches)) {
            // $this->modx->log(modX::LOG_LEVEL_INFO, "Relative Links & Anchors:\n" . print_r($matches, true));
            foreach ($matches[0] as $key => $match) {
                $tagPrefix = $matches[1][$key];
                $link = $matches[2][$key];
                $anchor = $matches[3][$key];
                if (strpos($link, '://') !== false)
                    continue;
                if (array_key_exists($link, $linkMap) &&
                    array_key_exists('dest_href', $linkMap[$link]) &&
                    $linkMap[$link]['dest_href']) {
                    $link = $linkMap[$link]['dest_href'];
                } elseif ($link === $pageTitle) {
                    $link = "/{$contextKey}/{$document->get('uri')}";
                } elseif (strpos($link, '[') === false) {
                    $targetDoc = $this->modx->getObject('modDocument',
                        array('context_key' => $this->modx->context->get('key'),
                            array('pagetitle' => str_replace('+', ' ', $link))));
                    if (!$targetDoc)
                        $targetDoc = $this->modx->getObject('modDocument',
                            array('pagetitle' => str_replace('+', ' ', $link)));
                    if ($targetDoc)
                        $link = "/{$contextKey}/{$targetDoc->get('uri')}";
                    unset($targetDoc);
                }
                if ($link !== $matches[2][$key]) {
                    $replaceWith = "{$tagPrefix}{$link}{$anchor}\"";
                    $replacedCount = 0;
                    $pageContent = str_replace($match, $replaceWith, $pageContent, $replacedCount);
                    $count += $replacedCount;
                }
            }
        }
        return $pageContent;
    }

    /* ideally this should be handled by a plugin */
    protected function fixLinksForBaseHref($contextKey, modDocument $document, $pageContent, &$count) {
        $replaceCount = 0;
        $pageContent = str_replace(
            array(
                'href="/',
                'src="/',
                'href="#',
            ),
            array(
                'href="',
                'src="',
                "href=\"{$contextKey}/{$document->get('uri')}#",
            ),
            $pageContent,
            $replaceCount
        );
        $count += $replaceCount;
        return $pageContent;
    }
}
