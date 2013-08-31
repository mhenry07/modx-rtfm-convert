<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\ContentExtractors\OldRtfmContentExtractor;
use RtfmConvert\HtmlTransformers\BrAtlForcedNewlineHtmlTransformer;
use RtfmConvert\HtmlTransformers\CodePanelHtmlTransformer;
use RtfmConvert\HtmlTransformers\FormattingElementHtmlTransformer;
use RtfmConvert\HtmlTransformers\NestedListHtmlTransformer;
use RtfmConvert\TextTransformers\CrlfToLfTextTransformer;
use RtfmConvert\TextTransformers\ModxTagsToEntitiesTextTransformer;
use RtfmConvert\TextTransformers\NbspTextTransformer;

class OldRtfmPageConverter {
    /** @var PageProcessor */
    protected $processor;

    public function __construct() {
        $processor = new PageProcessor();
        // pre-processing
        $processor->register(new OldRtfmContentExtractor());
        $processor->register(new NestedListHtmlTransformer());

        $processor->register(new CodePanelHtmlTransformer());
        $processor->register(new BrAtlForcedNewlineHtmlTransformer());
        $processor->register(new FormattingElementHtmlTransformer());

        // post-processing
        $processor->register(new ModxTagsToEntitiesTextTransformer());
        $processor->register(new NbspTextTransformer());
        $processor->register(new CrlfToLfTextTransformer());

        $this->processor = $processor;
    }

    public function convert($source, $dest) {
        $this->processor->processPage($source, $dest);
    }
}
