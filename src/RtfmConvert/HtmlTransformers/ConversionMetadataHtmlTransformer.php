<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\Analyzers\NewRtfmMetadataLoader;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

/**
 * Class ConversionMetadataHtmlTransformer
 * Injects conversion metadata, etc. obtained mainly from
 * OldRtfmContentExtractor and NewRtfmMetadataLoader.
 *
 * @package RtfmConvert\HtmlTransformers
 */
class ConversionMetadataHtmlTransformer extends AbstractHtmlTransformer {
    const SOURCE_PAGE_ID_ATTR = 'data-source-page-id';
    const SOURCE_PARENT_PAGE_ID_ATTR = 'data-source-parent-page-id';
    const SOURCE_SPACE_KEY_ATTR = 'data-source-space-key';
    const SOURCE_SPACE_NAME_ATTR = 'data-source-space-name';
    const SOURCE_MODIFICATION_INFO_ATTR = 'data-source-modification-info';
    const DEST_PAGE_ID_ATTR = 'data-dest-page-id';
    const DEST_MODIFICATION_INFO_ATTR = 'data-dest-modification-info';

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $stats = $pageData->getStats();
        if ($qp->top('head')->count() == 0)
            $qp->top('html')->prepend('<head><meta charset="utf-8" /><title></title></head>');

        $this->setTitle($qp, $stats);
        $this->addSourceLink($qp, $stats);
        $this->addSourceMetadata($qp, $stats);

        $this->addDestLink($qp, $stats);
        $this->addDestMetadata($qp, $stats);

        return $qp;
    }

    protected function setTitle(DOMQuery $qp, PageStatistics $stats) {
        $label = PageStatistics::SOURCE_PAGE_TITLE_LABEL;
        $pageTitle = $this->getStatValue($label, $stats);
        $qp->top('title')->text($pageTitle);
    }

    protected function addSourceLink(DOMQuery $qp, PageStatistics $stats) {
        $sourceUrl = $this->getStatValue(PageStatistics::SOURCE_URL_LABEL,
            $stats);
        if (isset($sourceUrl))
            $qp->top('head')->append(
                "<link rel=\"alternate\" title=\"source\" href=\"{$sourceUrl}\" />");
    }

    protected function addSourceMetadata(DOMQuery $qp, PageStatistics $stats) {
        $body = $qp->top('body');
        $this->setAttributeFromStat(self::SOURCE_PAGE_ID_ATTR,
            PageStatistics::SOURCE_PAGE_ID_LABEL, $body, $stats);
        $this->setAttributeFromStat(self::SOURCE_PARENT_PAGE_ID_ATTR,
            PageStatistics::SOURCE_PARENT_PAGE_ID_LABEL, $body, $stats);
        $this->setAttributeFromStat(self::SOURCE_SPACE_KEY_ATTR,
            PageStatistics::SOURCE_SPACE_KEY_LABEL, $body, $stats);
        $this->setAttributeFromStat(self::SOURCE_SPACE_NAME_ATTR,
            PageStatistics::SOURCE_SPACE_NAME_LABEL, $body, $stats);
        $this->setAttributeFromStat(self::SOURCE_MODIFICATION_INFO_ATTR,
            PageStatistics::SOURCE_MODIFICATION_INFO_LABEL, $body, $stats);
    }

    protected function addDestLink(DOMQuery $qp, PageStatistics $stats) {
        $destUrl = $this->getStatValue(NewRtfmMetadataLoader::DEST_URL_LABEL,
            $stats);
        if (isset($destUrl))
            $qp->top('head')->append(
                "<link rel=\"canonical\" title=\"dest\" href=\"{$destUrl}\" />");
    }

    protected function addDestMetadata(DOMQuery $qp, PageStatistics $stats) {
        $body = $qp->top('body');
        $this->setAttributeFromStat(self::DEST_PAGE_ID_ATTR,
            NewRtfmMetadataLoader::DEST_PAGE_ID_LABEL, $body, $stats);
        $this->setAttributeFromStat(self::DEST_MODIFICATION_INFO_ATTR,
            NewRtfmMetadataLoader::DEST_MODIFICATION_INFO_LABEL, $body, $stats);
    }

    protected function setAttributeFromStat($attributeName, $statLabel,
                                            DOMQuery $qp, PageStatistics $stats) {
        $value = $this->getStatValue($statLabel, $stats);
        if (isset($value))
            $qp->attr($attributeName, $value);
    }

    protected function getStatValue($label, PageStatistics $stats) {
        if (is_null($stats))
            return null;
        return $stats->getStat($label, PageStatistics::VALUE);
    }
}
