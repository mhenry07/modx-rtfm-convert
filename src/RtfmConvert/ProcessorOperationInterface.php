<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


interface ProcessorOperationInterface {
    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    function process($pageData);
}
