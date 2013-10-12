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

        foreach ($imports as $import) {
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
                $pageContent = $this->fixRelativeLinks($imports, $document,
                    $pageContent, $count);

            if ($this->config['fix_links_for_base_href'])
                $pageContent = $this->fixLinksForBaseHref($document,
                    $pageContent, $count);

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
    protected function fixRelativeLinks(array $pages, modDocument $document,
                                        $pageContent, &$count) {
        $patterns = array(
            '/(<a\b[^>]+\bhref=")([^#"]+)((?:#[^"]+)?)(")/i',
            "/(<a\b[^>]+\bhref=')([^#']+)((?:#[^']+)?)(')/i");
        foreach ($patterns as $pattern) {
            $matches = array();
            if (preg_match_all($pattern, $pageContent, $matches)) {
                // $this->modx->log(modX::LOG_LEVEL_INFO, "Relative Links & Anchors:\n" . print_r($matches, true));
                foreach ($matches[0] as $key => $match) {
                    $matchData = array(
                        'match' => $match,
                        'tagPrefix' => $matches[1][$key],
                        'link' => $matches[2][$key],
                        'anchor' => $matches[3][$key],
                        'endQuote' => $matches[4][$key]
                    );
                    $pageContent = $this->fixRelativeLink($pages, $document,
                        $pageContent, $matchData, $count);
                }
            }
        }
        return $pageContent;
    }

    protected function fixRelativeLink(array $pages, modDocument $document,
                                       $pageContent, array $matchData, &$count) {
        $link = $matchData['link'];
        if (strpos($link, '://') !== false)
            return $pageContent;
        $pageTitle = str_replace(' ', '+', $document->get('pagetitle'));
        $targetData = $this->getPageData($pages, $link);
        if ($targetData && array_key_exists('dest_id', $targetData) &&
            $targetData['dest_id']) {
            $destId = $targetData['dest_id'];
            if ($this->config['use_modx_link_tags']) {
                $link = $this->formatModxLinkTag($destId, $document);
            } elseif (isset($targetData['dest_href'])) {
                $link = $targetData['dest_href'];
            } else {
                /** @var modDocument $targetDoc */
                $targetDoc = $this->modx->getObject('modDocument', $destId);
                if ($targetDoc)
                    $link = $this->formatFriendlyUrl($targetDoc);
                unset($targetDoc);
            }
        } elseif ($link === $pageTitle) {
            $link = $this->formatLink($document, $document);
        } elseif (strpos($link, '[') === false) {
            /** @var modDocument $targetDoc */
            $targetDoc = $this->modx->getObject('modDocument',
                array('context_key' => $this->modx->context->get('key'),
                    array('pagetitle' => str_replace('+', ' ', $link))));
            if (!$targetDoc)
                $targetDoc = $this->modx->getObject('modDocument',
                    array('pagetitle' => str_replace('+', ' ', $link)));
            if ($targetDoc)
                $link = $this->formatLink($targetDoc, $document);
            unset($targetDoc);
        }
        if ($link !== $matchData['link']) {
            $replaceWith = "{$matchData['tagPrefix']}{$link}{$matchData['anchor']}{$matchData['endQuote']}";
            $replacedCount = 0;
            $pageContent = str_replace($matchData['match'], $replaceWith, $pageContent, $replacedCount);
            $count += $replacedCount;
        }
        return $pageContent;
    }

    protected function getPageData(array $pages, $sourceHref) {
        foreach ($pages as $data) {
            if ($data['source_href'] === $sourceHref)
                return $data;
        }
        return null;
    }

    protected function formatLink(modDocument $targetDocument,
                                  modDocument $currentDocument,
                                  $rootSlash = true) {
        if ($this->config['use_modx_link_tags'])
            return $this->formatModxLinkTag($targetDocument->get('id'),
                $currentDocument);
        return $this->formatFriendlyUrl($targetDocument, $rootSlash);
    }

    protected function formatModxLinkTag($targetId, modDocument $currentDocument) {
        if ($currentDocument->get('id') == $targetId)
            return '[[~[[*id]]]]';
        return "[[~{$targetId}]]";
    }

    protected function formatFriendlyUrl(modDocument $targetDocument,
                                         $rootSlash = true) {
        $contextKey = $targetDocument->get('context_key');
        $uri = $targetDocument->get('uri');
        $prefix = $rootSlash ? '/' : '';
        return "{$prefix}{$contextKey}/{$uri}";
    }

    /* ideally this should be handled by a plugin */
    protected function fixLinksForBaseHref(modDocument $document, $pageContent, &$count) {
        $replaceCount = 0;
        $selfLink = $this->formatLink($document, $document, false);
        $pageContent = str_replace(
            array(
                'href="/',
                'src="/',
                'href="#',
            ),
            array(
                'href="',
                'src="',
                "href=\"{$selfLink}#",
            ),
            $pageContent,
            $replaceCount
        );
        $count += $replaceCount;
        return $pageContent;
    }
}
