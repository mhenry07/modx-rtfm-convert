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
use RtfmConvert\HtmlTransformers\FormattingElementHtmlTransformer;
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


        // post-processing
        $processor->register(new HtmlTidyTextTransformer());
        $processor->register(new ModxTagsToEntitiesTextTransformer());
        $processor->register(new NbspTextTransformer());

        $this->processor = $processor;
    }

    public function convert($source, $dest) {
        $this->processor->processPage($source, $dest);
    }
}
