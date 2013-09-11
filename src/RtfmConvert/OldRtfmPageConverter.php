<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\ContentExtractors\OldRtfmContentExtractor;
use RtfmConvert\HtmlTransformers\BrAtlForcedNewlineHtmlTransformer;
use RtfmConvert\HtmlTransformers\CodePanelHtmlTransformer;
use RtfmConvert\HtmlTransformers\ConfluenceAsideHtmlTransformer;
use RtfmConvert\HtmlTransformers\ConfluenceTableHtmlTransformer;
use RtfmConvert\HtmlTransformers\ExternalLinkHtmlTransformer;
use RtfmConvert\HtmlTransformers\FormattingElementHtmlTransformer;
use RtfmConvert\HtmlTransformers\ImageHtmlTransformer;
use RtfmConvert\HtmlTransformers\NamedAnchorHtmlTransformer;
use RtfmConvert\HtmlTransformers\NestedListHtmlTransformer;
use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\FileIo;
use RtfmConvert\TextTransformers\HtmlTidyTextTransformer;
use RtfmConvert\TextTransformers\ModxTagsToEntitiesTextTransformer;
//use RtfmConvert\TextTransformers\NbspTextTransformer;

class OldRtfmPageConverter {
    /** @var PageProcessor */
    protected $processor;

    /** @var FileIo */
    protected $fileIo;

    public function __construct($cacheDir) {
        $pageLoader = new CachedPageLoader();
        $pageLoader->setBaseDirectory($cacheDir);
        $processor = new PageProcessor($pageLoader);
        $this->fileIo = new FileIo();
        // pre-processing
        $processor->register(new OldRtfmContentExtractor());
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
        // ConversionMetadataLoader (external lookup)
        // ConversionMetadataHtmlTransformer

        // post-processing
        $processor->register(new HtmlTidyTextTransformer());
        $processor->register(new ModxTagsToEntitiesTextTransformer());
        //$processor->register(new NbspTextTransformer());

        $this->processor = $processor;
    }

    public function convertPage($source, $dest) {
        $this->processor->processPage($source, $dest);
    }

    // TODO: add a PageProcessor::processPages method and call that
    public function convertAll($tocDir, $outputDir, $addHtmlExtension,
                               $statsFile) {
        echo 'Converting old MODX RTFM pages', PHP_EOL;
        echo 'Converted files will be written to: ',
            $this->fileIo->realpath($outputDir), PHP_EOL;
        echo PHP_EOL;

        $stats = array();

        $tocParser = new OldRtfmTocParser();
        $hrefs = $tocParser->parseTocDirectory($tocDir);
        foreach ($hrefs as $href) {
            $url = $href['url'];
            $destFile = $this->getDestinationFilename($url, $outputDir,
                $addHtmlExtension);
            $pageData = $this->processor->processPage($url, $destFile, false);

            $statsObj = $pageData->getStats();
            if (!is_null($statsObj))
                $stats[$href['href']] = $statsObj->getStats();
        }
        $this->saveStats($statsFile, $stats);
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
        echo PHP_EOL, 'Writing stats to: ', $dest, PHP_EOL;
        $json = json_encode($stats);
        $this->fileIo->write("$dest", $json);
    }
}
