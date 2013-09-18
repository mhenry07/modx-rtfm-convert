<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\RtfmQueryPath;

/**
 * Class PageTreeCleaner
 * Cleans up confluence plugin_pagetree. For use with PageTreeLoader.
 *
 * @package RtfmConvert\HtmlTransformers
 */
class PageTreeCleaner {
    protected $statsPrefix = 'pagetree: ';

    public function setStatsPrefix($prefix) {
        $this->statsPrefix = $prefix;
    }

    public function clean(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $pageData->beginTransform($qp);

        $expectedDiff = 0;
        foreach ($qp->find('div.plugin_pagetree') as $pageTree) {
            $pageData->incrementStat($this->statsPrefix . 'cleanup',
                PageStatistics::FOUND, 1);
            $allDescendants = RtfmQueryPath::countAll($pageTree);
            $uls = $pageTree->find('ul > li')->parent()->count();
            $lis = $pageTree->find('li')->count();

            // remove empty tree
            if ($lis == 0) {
                $pageData->incrementStat($this->statsPrefix . 'cleanup',
                    PageStatistics::TRANSFORM, 1, 'removed empty pagetree');
                $expectedDiff -= $allDescendants + 1;
                $pageTree->remove();
            } else {
                $pageData->incrementStat($this->statsPrefix . 'cleanup',
                    PageStatistics::TRANSFORM, 1, 'cleaned up pagetree');
                $expectedDiff -= $allDescendants - 2 * $lis - $uls;
            }
        }

        $pageTrees = $qp->find('div.plugin_pagetree')->not('#pagetree-error');
        if ($pageTrees->count() == 0) {
            $pageData->checkTransform($this->statsPrefix . 'cleanup',
                $qp, $expectedDiff);
            return $qp;
        }

        $fieldsets = $pageTrees->find('fieldset');
        if ($fieldsets->count() > 0)
            $fieldsets->remove();

        foreach ($pageTrees as $tree) {
            $firstNormalList = $tree->find('ul > li')->parent()->first();
            $depth = $firstNormalList->parentsUntil('div.plugin_pagetree')->count();
            while ($depth-- > 0)
                $firstNormalList->unwrap();
        }

        $childToggleContainers = $pageTrees
            ->find('.plugin_pagetree_childtoggle_container');
        if ($childToggleContainers->count() > 0)
            $childToggleContainers->remove();

        $childrenContainers = $pageTrees
            ->find('.plugin_pagetree_children_container');
        foreach ($childrenContainers as $container) {
            $ul = $container->children('ul');
            if ($ul->count() == 0)
                continue;
            if ($ul->contents()->count() == 0) {
                $container->remove();
            } else {
                $ul->unwrap();
            }
        }

        $childrenContents = $pageTrees->find('.plugin_pagetree_children_content');
        $childrenContents->find('a')->unwrap()->unwrap();

        $pageTrees->find('ul.plugin_pagetree_children_list')
            ->removeAttr('class')->removeAttr('id');

        $pageData->checkTransform($this->statsPrefix . 'cleanup',
            $qp, $expectedDiff);
        return $qp;
    }
}
