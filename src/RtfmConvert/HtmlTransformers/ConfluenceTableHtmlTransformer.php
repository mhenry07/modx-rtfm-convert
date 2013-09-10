<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

class ConfluenceTableHtmlTransformer extends AbstractHtmlTransformer {

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $this->generateStatistics($pageData);
        $qp = $pageData->getHtmlQuery();
        $pageData->beginTransform($qp);

        $tables = $qp->find('div.table-wrap table.confluenceTable');
        $tableCount = $tables->count();

        $tables->unwrap()->removeClass('confluenceTable');
        $tables->find('th.confluenceTh')->removeClass('confluenceTh');
        $tables->find('td.confluenceTd')->removeClass('confluenceTd');

        $pageData->checkTransform('div.table-wrap table.confluenceTable', $qp,
            -$tableCount);
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $confluenceTables = $pageData
            ->getHtmlQuery('div.table-wrap table.confluenceTable');
        $pageData->addQueryStat('div.table-wrap table.confluenceTable',
            $confluenceTables, array(self::TRANSFORM_ALL => true));
    }
}
