<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;

// Note: oldrtfm does not seem to use danger
class ConfluenceAsideHtmlTransformer extends AbstractHtmlTransformer {
    private $types = array('danger', 'info', 'note', 'tip', 'warning');

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();
        $qp->find('div.panelMacro')->each(
            function ($index, $item) {
                $panel = qp($item);
                $table = $panel->find('table')->first();
                foreach ($this->types as $type) {
                    if ($table->attr('class') == "{$type}Macro") {
                        $panel->removeClass('panelMacro')->addClass($type);
                        $table->remove();
                        $table->find('td')->eq(1)->contents()
                            ->detach()->attach($panel);
                        break;
                    }
                }
            }
        );
        return $qp;
    }

    protected function generateStatistics(PageData $pageData) {
        // TODO: Implement generateStatistics() method.
    }
}
