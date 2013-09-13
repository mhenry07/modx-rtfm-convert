<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\Analyzers\DocumentOutliner;
use RtfmConvert\Analyzers\PreElementAnalyzer;
use RtfmConvert\Analyzers\TextConverter;
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

        // content extraction
        $processor->register(new OldRtfmContentExtractor());

        // initial analysis
        $processor->register(new DocumentOutliner('before: '));
        $processor->register(new PreElementAnalyzer('before: '));
        $processor->register(TextConverter::create('before', $textDir,
            $this->fileIo));

        // pre-processing
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

        $count = 0;
        $stats = array();

        $tocParser = new OldRtfmTocParser();
        $hrefs = $tocParser->parseTocDirectory($tocDir);
        foreach ($hrefs as $href) {
            $count++;
            $path = $href['href'];
            $url = $href['url'];
            $destFile = $this->getDestinationFilename($url, $outputDir,
                $addHtmlExtension);
            $pageStats = new PageStatistics();
            $pageStats->addValueStat(PageStatistics::PATH_LABEL, $path);
            $pageData = $this->processor->processPage($url, $destFile,
                $pageStats, false);

            $statsObj = $pageData->getStats();
            if (!is_null($statsObj))
                $stats[$path] = $statsObj->getStats();
        }
        $this->saveStats($statsFile, $stats);

        echo 'Processed ', $count, ' pages', PHP_EOL;
        $elapsedTime = time() - $startTime;
        echo 'Elapsed time: ', $elapsedTime, ' sec', PHP_EOL;
    }

    protected function getDestinationFilename($url, $baseDir, $addHtmlExtension) {
        $path = trim(parse_url($url, PHP_URL_PATH), '/');
        $urlQuery = parse_url($url, PHP_URL_QUERY);
        if ($urlQuery)
            $path = PathHelper::join($path, $urlQuery);
        if ($addHtmlExtension)
            $path .= '.html';
        return PathHelper::join($baseDir, $path);
    }

    /**
     * @param string $dest
     * @param array $stats
     */
    protected function saveStats($dest, array $stats) {
        echo PHP_EOL;
        echo 'Writing stats to: ', PathHelper::normalize($dest), PHP_EOL;
        $json = json_encode($stats);
        $this->fileIo->write("$dest", $json);
    }
}
