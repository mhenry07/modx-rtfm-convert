<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

/**
 * Class PageTreeCleaner
 * Cleans up confluence plugin_pagetree. For use with PageTreeLoader.
 *
 * @package RtfmConvert\HtmlTransformers
 */
class PageTreeCleaner {
    public function clean(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $pageTrees = $qp->find('div.plugin_pagetree');
        if ($pageTrees->count() == 0)
            return $qp;

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

        return $qp;
    }
}
