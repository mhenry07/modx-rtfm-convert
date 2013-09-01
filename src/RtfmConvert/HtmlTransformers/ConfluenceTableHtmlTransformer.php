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
        $table = $qp->find('div.table-wrap table.confluenceTable');
        $table->unwrap()
            ->removeClass('confluenceTable');
        $table->find('th.confluenceTh')->removeClass('confluenceTh');
        $table->find('td.confluenceTd')->removeClass('confluenceTd');

        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        $pageData->addSimpleStat('div.table-wrap table.confluenceTable', true);
    }
}
