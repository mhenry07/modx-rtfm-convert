<?php
/**
 * @author: Mike Henry
 *
 * Replace confluence html files with extracted content.
 */

namespace RtfmMerge;


use RtfmConvert\ContentExtractors\ModxRtfmContentExtractor;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\FileIo;
use RtfmConvert\OldRtfmTocParser;
use RtfmConvert\PageProcessor;
use RtfmConvert\PageStatistics;
use RtfmConvert\PathHelper;
use RtfmConvert\TextTransformers\HtmlTidyTextTransformer;

class ModxWebContentExtractor {
    protected $config;

    /** @var PageProcessor */
    protected $processor;

    /** @var FileIo */
    protected $fileIo;

    public function __construct(array $config) {
        $this->config = $config;
        $this->fileIo = new FileIo();

        $pageLoader = new CachedPageLoader();
        $pageLoader->setBaseDirectory($this->config['cache_dir']);
        $processor = new PageProcessor($pageLoader);
        $processor->register(new ModxRtfmContentExtractor('', true));

        $tidyConfig = array(
            'show-body-only' => true,
            'indent' => true,
            'indent-spaces' => 0
        );
        $processor->register(new HtmlTidyTextTransformer($tidyConfig));

        $this->processor = $processor;
    }

    public function extractSiteContent() {
        $startTime = time();
        echo 'Extracting MODX RTFM site content', PHP_EOL;
        echo date('D M d H:i:s Y'), PHP_EOL;
        echo PHP_EOL;

        $stats = array();
        $statsBytes = false;

        $outputDir = $this->config['output_dir'];
        $tocParser = new OldRtfmTocParser();
        $tocParser->setBaseUrl($this->config['url']);
        $hrefs = $tocParser->parseTocDirectory($this->config['toc_dir']);
        foreach ($hrefs as $href) {
            $path = $href['href'];
            $url = $href['url'];
            $destFile = PathHelper::getConversionFilename($url, $outputDir,
                true);
            $pageStats = new PageStatistics();
            $pageStats->addValueStat(PageStatistics::PATH_LABEL, $path);
            $pageData = $this->processor->processPage($url, $destFile,
                $pageStats, false);

            $statsObj = $pageData->getStats();
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
}
