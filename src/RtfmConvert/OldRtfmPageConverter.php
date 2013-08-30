<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\ContentExtractors\OldRtfmContentExtractor;
use RtfmConvert\HtmlTransformers\BrAtlForcedNewlineHtmlTransformer;
use RtfmConvert\HtmlTransformers\CodePanelHtmlTransformer;
use RtfmConvert\HtmlTransformers\FormattingElementHtmlTransformer;
use RtfmConvert\TextTransformers\CrlfToLfTextTransformer;
use RtfmConvert\TextTransformers\ModxTagsToEntitiesTextTransformer;

class OldRtfmPageConverter {
    /** @var PageProcessor */
    protected $processor;

    public function __construct() {
        $processor = new PageProcessor();
        $processor->register(new OldRtfmContentExtractor());
        $processor->register(new CodePanelHtmlTransformer());
        $processor->register(new BrAtlForcedNewlineHtmlTransformer());
        $processor->register(new FormattingElementHtmlTransformer());
        $processor->register(new ModxTagsToEntitiesTextTransformer());
        $processor->register(new CrlfToLfTextTransformer());
        $this->processor = $processor;
    }

    public function convert($source, $dest) {
        $this->processor->processPage($source, $dest);
    }
}
