<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\PageLoaderInterface;
use RtfmConvert\PageData;

/**
 * Class PageTreeHtmlTransformer
 * Retrieves data for and builds a plugin_pagetree navigation tree
 *
 * @package RtfmConvert\HtmlTransformers
 */
class PageTreeHtmlTransformer extends AbstractHtmlTransformer {
    protected $pageLoader;
    protected $statsPrefix = 'pagetree: ';

    function __construct(PageLoaderInterface $pageLoader) {
        $this->pageLoader = $pageLoader ? : new CachedPageLoader();
    }

    public function setStatsPrefix($prefix) {
        $this->pageLoader->setStatsPrefix($prefix);
        $this->statsPrefix = $prefix;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $loader = new PageTreeLoader($this->pageLoader);
        $loader->setStatsPrefix($this->statsPrefix);
        $qp = $loader->load($pageData);

        $pageData = new $pageData($qp, $pageData->getStats());
        $cleaner = new PageTreeCleaner();
        $qp = $cleaner->clean($pageData);

        return $qp;
    }
}
