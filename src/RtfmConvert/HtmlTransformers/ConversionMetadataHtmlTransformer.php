<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use QueryPath\DOMQuery;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

/**
 * Class ConversionMetadataHtmlTransformer
 * Injects conversion metadata, etc.
 * @todo: inject destination metadata (page-id, etc.)
 *
 * @package RtfmConvert\HtmlTransformers
 */
class ConversionMetadataHtmlTransformer extends AbstractHtmlTransformer {
    const SOURCE_PAGE_ID_ATTR = 'data-source-page-id';
    const SOURCE_PARENT_PAGE_ID_ATTR = 'data-source-parent-page-id';
    const SOURCE_SPACE_KEY_ATTR = 'data-source-space-key';
    const SOURCE_SPACE_NAME_ATTR = 'data-source-space-name';
    const SOURCE_MODIFICATION_INFO_ATTR = 'data-source-modification-info';

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $stats = $pageData->getStats();
        $this->setTitle($qp, $stats);
        $this->addMetaCharset($qp, 'utf-8');
        $this->addSourceLink($qp, $stats);
        $this->addSourceMetadata($qp, $stats);
        return $qp;
    }

    protected function setTitle(DOMQuery $qp, PageStatistics $stats) {
        $label = PageStatistics::SOURCE_PAGE_TITLE_LABEL;
        $pageTitle = $this->getStatValue($label, $stats);
        $qp->top('title')->text($pageTitle);
    }

    // use self-closing meta tag since prepend requires valid XML
    protected function addMetaCharset(DOMQuery $qp, $charset) {
        $qp->top('head')->prepend("<meta charset=\"{$charset}\" />");
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

    protected function setAttributeFromStat($attributeName, $statLabel,
                                            DOMQuery $qp, PageStatistics $stats) {
        $value = $this->getStatValue($statLabel, $stats);
        if (isset($value))
            $qp->attr($attributeName, $value);
    }

    protected function getStatValue($label, PageStatistics $stats) {
        if (is_null($stats))
            return null;
        $statsArray = $stats->getStats();
        if (!array_key_exists($label, $statsArray))
            return null;
        return $statsArray[$label][PageStatistics::VALUE];
    }
}
