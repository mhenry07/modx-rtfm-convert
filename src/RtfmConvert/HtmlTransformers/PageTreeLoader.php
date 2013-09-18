<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\PageLoaderInterface;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

/**
 * Class PageTreeLoader
 * Loads AJAX navigation trees from Confluence.
 *
 * @package RtfmConvert\HtmlTransformers
 */
class PageTreeLoader {
    const DEFAULT_BASE_URL = 'http://oldrtfm.modx.com';

    protected $pageLoader;
    protected $statsPrefix = 'pagetree: ';

    function __construct(PageLoaderInterface $pageLoader) {
        $this->pageLoader = $pageLoader ? : new CachedPageLoader();
    }

    public function setStatsPrefix($prefix) {
        $this->pageLoader->setStatsPrefix($prefix);
        $this->statsPrefix = $prefix;
    }

    // load and inject data for all available plugin_pagetrees
    public function load(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $stats = $pageData->getStats();

        $treeId = 1;
        $pageTrees = $qp->find('div.plugin_pagetree');
        $pageData->addQueryStat($this->statsPrefix . 'div.plugin_pagetree',
            $pageTrees);
        foreach ($pageTrees as $pageTree) {
            if ($pageTree->find('#pagetree-error')->count() > 0) {
                $pageData->incrementStat(
                    $this->statsPrefix . 'div.plugin_pagetree',
                    PageStatistics::WARNING, 1, 'pagetree error');
                $treeId++;
                continue;
            }
            $this->loadTree($pageTree, $stats, $treeId);
            $pageData->incrementStat($this->statsPrefix . 'div.plugin_pagetree',
                PageStatistics::TRANSFORM, 1, 'loaded');
            $pageData->addQueryStat($this->statsPrefix . "{$treeId}: li",
                $pageTree->find('li'),
                array(PageStatistics::TRANSFORM_ALL => true,
                    PageStatistics::TRANSFORM_MESSAGES => 'loaded'));
            $treeId++;
        }

        return $qp;
    }

    protected function getBaseUrl(PageStatistics $stats) {
        $sourceUrl = $stats->getStat(PageStatistics::SOURCE_URL_LABEL,
            PageStatistics::VALUE);
        if (is_null($sourceUrl))
            return self::DEFAULT_BASE_URL;
        return parse_url($sourceUrl, PHP_URL_SCHEME) . '://' .
            parse_url($sourceUrl, PHP_URL_HOST);
    }

    protected function buildRequestUrl(DOMQuery $pageTree, PageStatistics $stats,
                                       $treeId = null, $pageId = null) {
        $loadAncestors = isset($pageId) ? false : true;
        $requestUrl = $this->getBaseUrl($stats);
        $requestUrl .= $pageTree->find('input[name="treeRequestId"]')
            ->attr('value');

        $noRoot = $pageTree->find('input[name="noRoot"]')->attr('value');
        $hasRoot = $noRoot === 'false' ? 'true' : 'false';
        $requestUrl .= '&hasRoot=' . $hasRoot;

        if (is_null($pageId))
            $pageId = $pageTree->find('input[name="rootPageId"]')->attr('value');
        $requestUrl .= '&pageId=' . $pageId;

        if (is_null($treeId))
            $treeId = $pageTree->find('input[name="treeId"]')->attr('value') ? : '1';
        $requestUrl .= '&treeId=' . $treeId;

        $startDepth = $pageTree->find('input[name="startDepth"]')->attr('value');
        $requestUrl .= '&startDepth=' . $startDepth;

        if ($loadAncestors) {
            $pageTree->find('input[name="ancestorId"]')->each(
                function ($index, $item) use (&$requestUrl) {
                    $requestUrl .= '&ancestors=' . qp($item)->attr('value');
                }
            );
        }
        return $requestUrl;
    }

    protected function loadTree(DOMQuery $pageTree, PageStatistics $stats,
                                $treeId) {
        $requests = array();

        // build top level
        $requestUrl = $this->buildRequestUrl($pageTree, $stats, $treeId);
        $response = $this->pageLoader->get($requestUrl);
        $requests[] = $requestUrl;
        $pageTree->find('.plugin_pagetree_children')->first()->html($response);

        // build descendants
        while (true) {
            $toggle = $pageTree->find('.plugin_pagetree_childtoggle.icon-plus')
                ->first();
            if ($toggle->count() == 0)
                break;
            $this->loadChild($pageTree, $toggle, $stats, $requests);
        }

        $stats->addValueStat($this->statsPrefix . "{$treeId}: requests",
            count($requests), array(PageStatistics::DATA => $requests));
    }

    protected function loadChild(DOMQuery $pageTree, DOMQuery $toggle,
                                 PageStatistics $stats, array &$requests) {
        $pageId = null;
        $treeId = null;
        $toggleId = $toggle->attr('id');
        if (preg_match('/^plusminus(\d+)-(\d+)$/', $toggleId, $matches)) {
            $pageId = $matches[1];
            $treeId = $matches[2];
        }
        $requestUrl = $this->buildRequestUrl($pageTree, $stats, $treeId,
            $pageId);
        $response = $this->pageLoader->get($requestUrl);
        $requests[] = $requestUrl;
        $toggle->parent('li')->find('div.plugin_pagetree_children_container')
            ->first()->html($response);
        $toggle->removeClass('icon-plus');
    }
}
