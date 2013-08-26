<?php
/**
 * User: mhenry
 * Date: 8/24/13
 */

namespace RtfmConvert\ContentExtractors;


interface ContentExtractorInterface {
    function extract($html);
}
