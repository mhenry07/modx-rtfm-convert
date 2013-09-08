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
use RtfmConvert\TextTransformers\HtmlTidyTextTransformer;
use RtfmConvert\TextTransformers\ModxTagsToEntitiesTextTransformer;
use RtfmConvert\TextTransformers\NbspTextTransformer;

class OldRtfmPageConverter {
    /** @var PageProcessor */
    protected $processor;

    public function __construct($cacheDir) {
        $pageLoader = new CachedPageLoader();
        $pageLoader->setBaseDirectory($cacheDir);
        $processor = new PageProcessor($pageLoader);
        // pre-processing
        $processor->register(new OldRtfmContentExtractor());
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


        // post-processing
        $processor->register(new HtmlTidyTextTransformer());
        $processor->register(new ModxTagsToEntitiesTextTransformer());
        $processor->register(new NbspTextTransformer());

        $this->processor = $processor;
    }

    public function convertPage($source, $dest) {
        $this->processor->processPage($source, $dest);
    }

    // TODO: add a PageProcessor::processPages method and call that
    // TODO: add an isBatch param to processPage and delay writing stats if true
    // TODO: write all stats to a single file instead of one per page
    public function convertAll($tocDir, $outputDir, $addHtmlExtension) {
        $tocParser = new OldRtfmTocParser();
        $hrefs = $tocParser->parseTocDirectory($tocDir);
        foreach ($hrefs as $href) {
            $url = $href['url'];
            $destFile = $this->getDestinationFilename($url, $outputDir,
                $addHtmlExtension);
            $this->processor->processPage($url, $destFile);
        }
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
}
