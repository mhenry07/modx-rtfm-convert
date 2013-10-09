<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use Exception;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\PageLoaderInterface;
use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;
use RtfmConvert\RtfmQueryPath;

class NewRtfmMetadataLoader implements ProcessorOperationInterface {
    const DEST_URL_LABEL = 'dest: url';
    const DEST_PAGE_ID_LABEL = 'dest: page-id';
    const DEST_AUTHOR_LABEL = 'dest: author';
    const DEST_TITLE_LABEL = 'dest: title';
    const DEST_MODIFICATION_INFO_LABEL = 'dest: modification-info';

    protected $baseUrl = 'http://rtfm.modx.com';
    protected $statsPrefix = 'dest: ';
    protected $pageLoader;


    public function __construct(PageLoaderInterface $pageLoader) {
        $this->pageLoader = $pageLoader ? : new CachedPageLoader();
    }

    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function setCacheDirectory($cacheDirectory) {
        if ($this->pageLoader instanceof CachedPageLoader)
            $this->pageLoader->setBaseDirectory($cacheDirectory);
    }

    public function setStatsPrefix($prefix) {
        $this->pageLoader->setStatsPrefix($prefix);
        $this->statsPrefix = $prefix;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $stats = $pageData->getStats();
        $path = $pageData->getStats()
            ->getStat(PageStatistics::PATH_LABEL, PageStatistics::VALUE);
        $url = $this->baseUrl . $path;

        $html = '';
        try {
            $html = $this->pageLoader->get($url, $stats);
        } catch (Exception $e) {
            $prefix = $this->statsPrefix;
            echo '  ', $prefix, $e->getMessage(), PHP_EOL;
            $pageData->addValueStat("{$prefix}Errors", null,
                array(PageStatistics::ERROR => 1,
                    PageStatistics::ERROR_MESSAGES => $e->getMessage()));
            return $pageData;
        }

        $qp = RtfmQueryPath::htmlqp($html);
        $head = $qp->top('head');
        $body = $qp->top('body');

        $canonicalUrl = $head->find('link[rel="canonical"]')
            ->attr('href');
        // fix canonical URL when using dev site
        $canonicalUrl = str_replace('http://rtfm.modx/', "{$this->baseUrl}/",
            $canonicalUrl);
        $stats->addValueStat(self::DEST_URL_LABEL, $canonicalUrl);

        $pageId = $body->attr('data-page-id');
        $stats->addValueStat(self::DEST_PAGE_ID_LABEL, $pageId);

        $author = $head->find('meta[name="author"]')->attr('content');
        $stats->addValueStat(self::DEST_AUTHOR_LABEL, $author);

        $header = $body->find('.content header');
        $title = trim($header->find('h1')->text());
        $stats->addValueStat(self::DEST_TITLE_LABEL, $title);

        $modificationInfo = trim($header->find('h5')->text());
        $stats->addValueStat(self::DEST_MODIFICATION_INFO_LABEL,
            $modificationInfo);

        return $pageData;
    }
}
