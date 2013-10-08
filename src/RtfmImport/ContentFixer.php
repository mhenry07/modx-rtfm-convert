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
            if (!$modx->switchContext($contextKey)) {
                echo "ERROR switching to context {$contextKey} to organize imported resource\n";
                continue;
            }

            /** @var modDocument $document */
            $document = $modx->getObject('modDocument', array('id' => $import['dest_id']));
            $pageContent = $document->getContent();

            $count = 0;
            $pageContent = $this->fixRelativeLinks($imports, $pageContent, $count);

            if ($this->config['fix_links_for_base_href'])
                $pageContent = $this->fixLinksForBaseHref($contextKey, $document, $pageContent, $count);

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
    // TODO: fix links to non-imported pages (e.g. Home)
    protected function fixRelativeLinks(array $linkMap, $pageContent, &$count) {
        $matches = array();
        $replacedCount = 0;
        if (preg_match_all('/(<a\b[^>]+\bhref=")([^#"]+)((?:#[^"]+)?)"/i', $pageContent, $matches)) {
            // $this->modx->log(modX::LOG_LEVEL_INFO, "Relative Links & Anchors:\n" . print_r($matches, true));
            foreach ($matches[0] as $key => $match) {
                $tagPrefix = $matches[1][$key];
                $link = $matches[2][$key];
                $anchor = $matches[3][$key];
                if (strpos($link, '://') !== false)
                    continue;
                if (array_key_exists($link, $linkMap)) {
                    $link = $linkMap['dest_href'];
                    $replaceWith = "{$tagPrefix}{$link}{$anchor}\"";
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
