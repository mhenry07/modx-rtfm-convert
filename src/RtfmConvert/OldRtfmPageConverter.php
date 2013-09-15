<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\Analyzers\DocumentOutliner;
use RtfmConvert\Analyzers\PreElementAnalyzer;
use RtfmConvert\Analyzers\TextConverter;
use RtfmConvert\Analyzers\TextDiffAnalyzer;
use RtfmConvert\ContentExtractors\OldRtfmContentExtractor;
use RtfmConvert\HtmlTransformers\BrAtlForcedNewlineHtmlTransformer;
use RtfmConvert\HtmlTransformers\CodePanelHtmlTransformer;
use RtfmConvert\HtmlTransformers\ConfluenceAsideHtmlTransformer;
use RtfmConvert\HtmlTransformers\ConfluenceTableHtmlTransformer;
use RtfmConvert\HtmlTransformers\ConversionMetadataHtmlTransformer;
use RtfmConvert\HtmlTransformers\ExternalLinkHtmlTransformer;
use RtfmConvert\HtmlTransformers\FormattingElementHtmlTransformer;
use RtfmConvert\HtmlTransformers\ImageHtmlTransformer;
use RtfmConvert\HtmlTransformers\NamedAnchorHtmlTransformer;
use RtfmConvert\HtmlTransformers\NestedListHtmlTransformer;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\FileIo;
use RtfmConvert\TextTransformers\HtmlTidyTextTransformer;
use RtfmConvert\TextTransformers\CharsetDeclarationTextTransformer;
use RtfmConvert\TextTransformers\ModxTagsToEntitiesTextTransformer;

class OldRtfmPageConverter {
    /** @var PageProcessor */
    protected $processor;

    /** @var FileIo */
    protected $fileIo;

    public function __construct($cacheDir, $textDir) {
        $pageLoader = new CachedPageLoader();
        $pageLoader->setBaseDirectory($cacheDir);
        $processor = new PageProcessor($pageLoader);
        $this->fileIo = new FileIo();

        // initial pre-processing
        $processor->register(new CharsetDeclarationTextTransformer());

        // content extraction
        $processor->register(new OldRtfmContentExtractor());

        // initial analysis
        $processor->register(new DocumentOutliner('before: '));
        $processor->register(new PreElementAnalyzer('before: '));
        $processor->register(TextConverter::create('before', $textDir,
            $this->fileIo));

        // main pre-processing
        // PageTreeHtmlTransformer (external requests) // note: will require cleanup (nested lists, etc.)
        $processor->register(new NestedListHtmlTransformer());

        // main processing
        $processor->register(new BrAtlForcedNewlineHtmlTransformer());
        $processor->register(new FormattingElementHtmlTransformer());
        $processor->register(new CodePanelHtmlTransformer());
        $processor->register(new ConfluenceTableHtmlTransformer());
        $processor->register(new ConfluenceAsideHtmlTransformer());
        $processor->register(new NamedAnchorHtmlTransformer());
        $processor->register(new ImageHtmlTransformer());
        $processor->register(new ExternalLinkHtmlTransformer());

        // RtfmLinkHtmlTransformer (external lookup) // if using [[~id]] links, they would have to be ignored by ModxTagsToEntitiesTextTransformer or done as a text transformer after ModxTagsToEntitiesTextTransformer
        // DestinationMetadataLoader (external lookup)

        $processor->register(new ConversionMetadataHtmlTransformer());

        // post-processing
        $processor->register(new HtmlTidyTextTransformer());
        $processor->register(new ModxTagsToEntitiesTextTransformer());

        // final analysis
        $processor->register(new DocumentOutliner('after: ', 'before: '));
        $processor->register(new PreElementAnalyzer('after: ', 'before: '));
        $processor->register(TextConverter::create('after', $textDir,
            $this->fileIo));
        $processor->register(TextDiffAnalyzer::create('before', 'after',
            $textDir, $this->fileIo));

        $this->processor = $processor;
    }

    public function convertPage($source, $dest) {
        $this->processor->processPage($source, $dest);
    }

    // TODO: add a PageProcessor::processPages method and call that
    public function convertAll($tocDir, $outputDir, $addHtmlExtension,
                               $statsFile) {
        $startTime = time();
        echo 'Converting old MODX RTFM pages', PHP_EOL;
        echo date('D M d H:i:s Y'), PHP_EOL;
        echo 'Converted files will be written to: ',
            PathHelper::normalize($outputDir), PHP_EOL;
        echo PHP_EOL;

        $stats = array();
        $statsBytes = false;

        $tocParser = new OldRtfmTocParser();
        $hrefs = $tocParser->parseTocDirectory($tocDir);
        foreach ($hrefs as $href) {
            $path = $href['href'];
            $url = $href['url'];
            $destFile = $this->getDestinationFilename($url, $outputDir,
                $addHtmlExtension);
            $pageStats = new PageStatistics();
            $pageStats->addValueStat(PageStatistics::PATH_LABEL, $path);
            $pageData = $this->processor->processPage($url, $destFile,
                $pageStats, false);

            $statsObj = $pageData->getStats();
            if (isset($statsObj)) {
                $stats[$path] = $statsObj->getStats();
                $statsBytes = $this->saveStats($statsFile, $stats);
            }
        }

        $elapsedTime = time() - $startTime;
        $this->printSummary($stats, $statsFile, $statsBytes, $elapsedTime);
    }

    protected function getDestinationFilename($url, $baseDir, $addHtmlExtension) {
        $urlPath = parse_url($url, PHP_URL_PATH);
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        $relativeUrl = preg_replace('#/$#', '', $urlPath);
        if ($urlQuery)
            $relativeUrl .= '?' . $urlQuery;
        $filePath = PathHelper::convertRelativeUrlToFilePath($relativeUrl);
        if ($addHtmlExtension)
            $filePath .= '.html';
        return PathHelper::join($baseDir, $filePath);
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
            $elapsedTimeString = $elapsedTime / 60 . ' minutes ' .
                $elapsedTime % 60 . ' seconds';
        echo 'Elapsed time: ', $elapsedTimeString;
        echo ' (avg. ' . $elapsedTime * 1.0 / $count . ' seconds/page)';
        echo PHP_EOL;
    }
}
