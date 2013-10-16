<?php
/**
 * @author: Mike Henry
 *
 * Page-by-page comparison between two MODX RTFM sites including: text diffs,
 * document outlines and pre element counts
 */

namespace RtfmCompare;


use RtfmConvert\Analyzers\DocumentOutliner;
use RtfmConvert\Analyzers\NewRtfmMetadataLoader;
use RtfmConvert\Analyzers\PreElementAnalyzer;
use RtfmConvert\Analyzers\TextConverter;
use RtfmConvert\Analyzers\TextDiffAnalyzer;
use RtfmConvert\ContentExtractors\ModxRtfmContentExtractor;
use RtfmConvert\ContentExtractors\OldRtfmContentExtractor;
use RtfmConvert\HtmlTransformers\PageTreeHtmlTransformer;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\FileIo;
use RtfmConvert\OldRtfmTocParser;
use RtfmConvert\PageProcessor;
use RtfmConvert\PageStatistics;
use RtfmConvert\PathHelper;
use RtfmConvert\TextTransformers\CharsetDeclarationTextTransformer;

class SiteComparer {
    protected $config;
    protected $site1;
    protected $site2;

    /** @var PageProcessor */
    protected $processor1;

    /** @var PageProcessor */
    protected $processor2;

    /** @var FileIo */
    protected $fileIo;

    public function __construct($site1, $site2, array $config) {
        $this->config = $config;
        $this->site1 = $site1;
        $this->site2 = $site2;
        $this->fileIo = new FileIo();

        $this->processor1 = $this->createProcessor($site1);
        $this->processor2 = $this->createProcessor($site2, $site1);
    }

    // should I try to strip converted plugin_pagetrees (.page-toc, .see-also) before diffing?
    // should I ignore h3. child pages item when comparing outlines?
    protected function createProcessor($site, $otherSite = null) {
        $statPrefix = $this->formatStatPrefix($site);
        $otherStatPrefix = $this->formatStatPrefix($otherSite);

        $pageLoader = new CachedPageLoader();
        $pageLoader->setBaseDirectory($this->config['cache_dir']);
        $pageLoader->setStatsPrefix($statPrefix);
        $processor = new PageProcessor($pageLoader);
        $processor->setStatsPrefix($statPrefix);

        $processor->register(new CharsetDeclarationTextTransformer());

        $metadataLoader = $this->createMetadataLoader($site);
        if ($metadataLoader)
            $processor->register($metadataLoader);

        $processor->register($this->createContentExtractor($site));

        if ($this->getSiteConfig($site, 'build_pagetrees'))
            $processor->register($this->createPageTreeHtmlTransformer($site));

        $processor->register(
            new DocumentOutliner($statPrefix, $otherStatPrefix));
        $processor->register(
            new PreElementAnalyzer($statPrefix, $otherStatPrefix));
        $processor->register(TextConverter::create($site,
            $this->config['text_dir'], $this->fileIo));

        if (isset($otherSite))
            $processor->register(TextDiffAnalyzer::create($otherSite, $site,
                $this->config['text_dir'], $this->fileIo));

        return $processor;
    }

    public function comparePages($url1, $url2) {
        $pageStats = new PageStatistics();
        $pageData1 = $this->processor1->processPage($url1, null, $pageStats,
            false);
        $pageData2 = $this->processor1->processPage($url2, null,
            $pageData1->getStats(), false);

        $statsObj = $pageData2->getStats();
        if (isset($statsObj))
            $this->saveStats(
                $this->config['stats_file'], $statsObj->getStats());
    }

    public function compareSites() {
        $startTime = time();
        echo 'Comparing MODX RTFM sites', PHP_EOL;
        echo date('D M d H:i:s Y'), PHP_EOL;
        echo 'Comparison files will be written to: ',
            PathHelper::normalize($this->config['text_dir']), PHP_EOL;
        echo PHP_EOL;

        $stats = array();
        $statsBytes = false;

        $tocParser = new OldRtfmTocParser();
        $tocParser->setBaseUrl($this->getSiteConfig($this->site1, 'url'));
        $hrefs = $tocParser->parseTocDirectory($this->config['toc_dir']);
        foreach ($hrefs as $href) {
            $path = $href['href'];
            $url1 = $this->formatUrl($this->site1, $path);
            $url2 = $this->formatUrl($this->site2, $path);
            $pageStats = new PageStatistics();
            $pageStats->addValueStat(PageStatistics::PATH_LABEL, $path);
            $pageData1 = $this->processor1->processPage($url1, null,
                $pageStats, false);
            $pageData2 = $this->processor2->processPage($url2, null,
                $pageData1->getStats(), false);

            $statsObj = $pageData2->getStats();
            if (isset($statsObj)) {
                $stats[$path] = $statsObj->getStats();
                $statsBytes = $this->saveStats($this->config['stats_file'],
                    $stats);
            }
        }

        $elapsedTime = time() - $startTime;
        $this->printSummary($stats, $this->config['stats_file'], $statsBytes,
            $elapsedTime);
    }

    /**
     * @param string $dest
     * @param array $stats
     * @return int|bool
     */
    protected function saveStats($dest, array $stats) {
        $json = json_encode($stats);
        if (json_last_error() != JSON_ERROR_NONE) {
            echo '  JSON error: ', json_last_error_msg(), PHP_EOL;
            echo '  Error: Stats file not saved', PHP_EOL;
            return false;
        }
        return $this->fileIo->write($dest, $json);
    }

    /**
     * @param array $stats
     * @param string $statsFile
     * @param int|bool $statsBytes
     * @param int $elapsedTime
     */
    protected function printSummary(array $stats, $statsFile, $statsBytes,
                                    $elapsedTime) {
        $pagesWithErrors = count(array_filter($stats, function ($pageStats) {
            return PageStatistics::countErrors($pageStats) > 0;
        }));
        $pagesWithWarnings = count(array_filter($stats, function ($pageStats) {
            return PageStatistics::countWarnings($pageStats) > 0;
        }));

        echo PHP_EOL;
        if ($statsBytes)
            echo 'Stats saved to: ', PathHelper::normalize($statsFile), PHP_EOL;

        $count = count($stats);
        echo 'Processed ', $count, ' pages';
        if ($pagesWithErrors > 0)
            echo ", {$pagesWithErrors} with errors";
        if ($pagesWithWarnings > 0)
            echo ", {$pagesWithWarnings} with warnings";
        echo PHP_EOL;

        $elapsedTimeString = $elapsedTime . ' seconds';
        if ($elapsedTime > 60)
            $elapsedTimeString = intval($elapsedTime / 60) . ' minutes ' .
                $elapsedTime % 60 . ' seconds';
        echo 'Elapsed time: ', $elapsedTimeString;
        if ($count > 0)
            echo ' (avg. ' . $elapsedTime * 1.0 / $count . ' seconds/page)';
        echo PHP_EOL;
    }

    protected function createMetadataLoader($site) {
        // note: confluence metadata is loaded by OldRtfmContentExtractor
        if ($this->getSiteConfig($site, 'type') == 'confluence')
            return null;
        $metadataLoader = new NewRtfmMetadataLoader(new CachedPageLoader());
        $metadataLoader->setBaseUrl($this->getSiteConfig($site, 'url'));
        $metadataLoader->setStatsPrefix($this->formatStatPrefix($site));
        $metadataLoader->setCacheDirectory($this->config['cache_dir']);
        $metadataLoader->setUseHtmlExtensions(
            $this->getSiteConfig($site, 'use_html_extensions'));
        return $metadataLoader;
    }

    protected function createContentExtractor($site) {
        $statPrefix = $this->formatStatPrefix($site);
        if ($this->getSiteConfig($site, 'type') == 'confluence')
            return new OldRtfmContentExtractor($statPrefix);
        return new ModxRtfmContentExtractor($statPrefix,
            $this->getSiteConfig($site, 'exclude_child_pages_section'));
    }

    protected function formatStatPrefix($site) {
        if (is_null($site))
            return null;
        return "[{$site}] ";
    }

    protected function getSiteConfig($site, $configKey = null) {
        $siteConfig = $this->config['sites'][$site];
        if (isset($configKey)) {
            if (!array_key_exists($configKey, $siteConfig))
                return null;
            return $siteConfig[$configKey];
        }
        return $siteConfig;
    }

    protected function createPageTreeHtmlTransformer($site) {
        $pageLoader = new CachedPageLoader();
        $pageLoader->setBaseDirectory($this->config['cache_dir']);
        $pagetreeTransformer = new PageTreeHtmlTransformer($pageLoader);
        $pagetreeTransformer->setStatsPrefix(
            $this->formatStatPrefix($site) . ' pagetree: ');
        return $pagetreeTransformer;
    }

    protected function formatUrl($site, $path) {
        $baseUrl = $this->getSiteConfig($site, 'url');
        $useHtmlExtensions = $this->getSiteConfig($site, 'use_html_extensions');
        return PathHelper::formatUrl($baseUrl, $path, $useHtmlExtensions);
    }
}
