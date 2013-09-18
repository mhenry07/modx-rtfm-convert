<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageStatistics;
use RtfmConvert\ProcessorOperationInterface;

/**
 * Class ErrorClassAnalyzer
 * Adds warnings for div.error, span.error, etc.
 * Unknown macro example: http://oldrtfm.modx.com/display/ADDON/YAMS+How+To
 * Broken wikilink example: http://oldrtfm.modx.com/display/revolution20/Git+Installation
 *
 * @package RtfmConvert\Analyzers
 */
class ErrorClassAnalyzer implements ProcessorOperationInterface {

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $qp = $pageData->getHtmlQuery();

        $errorClasses = $qp->find('.error');
        if ($errorClasses->count() > 0)
        $pageData->addQueryStat('.error', $errorClasses);
        $errorClasses->each(function ($index, $item) use ($pageData) {
            $qp = qp($item);
            $tag = $qp->tag();
            $msg = "{$tag}.error";

            if ($tag == 'div' &&
                preg_match('/\bUnknown macro: \{[^}]*}/', $qp->text(), $matches))
                $msg .= ': ' . $matches[0];

            $wikilinkPattern = '/^(?:\[|&#91;)(?!&#9[13];)[^\[\]]+(?:]|&#93;)$/';
            if ($tag == 'span' && preg_match($wikilinkPattern, $qp->text()))
                $msg .= ': broken wikilink';

            $pageData->incrementStat('.error', PageStatistics::WARNING, 1,
                $msg);
        });

        return $pageData;
    }
}
