<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\PageData;
use RtfmConvert\RtfmQueryPath;

// Note: oldrtfm does not seem to use danger
class ConfluenceAsideHtmlTransformer extends AbstractHtmlTransformer {
    private $types = array('danger', 'info', 'note', 'tip', 'warning');

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \QueryPath\DOMQuery
     */
    public function transform(PageData $pageData) {
        $qp = $pageData->getHtmlQuery();

        $asides = $qp->find('div.panelMacro');
        // I think it's -8 per .panelMacro
        $expectedDiff = -(RtfmQueryPath::countAll($asides) -
            RtfmQueryPath::countAll($asides->find('table > tr > td:last-child')));
        $pageData->addQueryStat('asides', $asides);

        $pageData->beginTransform($qp);
        $asides->each(function ($index, $item) use ($pageData) {
            $panel = qp($item);
            $table = $panel->find('table')->first();
            foreach ($this->types as $type) {
                if ($table->attr('class') == "{$type}Macro") {
                    $pageData->incrementStat('asides', self::TRANSFORM, 1,
                        "extracting content to div.{$type}");
                    $panel->removeClass('panelMacro')->addClass($type);
                    $table->remove();
                    $table->find('td')->last()->contents()
                        ->detach()->attach($panel);
                    break;
                }
            }
        });
        $pageData->checkTransform('asides', $qp, $expectedDiff);

        return $qp;
    }
}
