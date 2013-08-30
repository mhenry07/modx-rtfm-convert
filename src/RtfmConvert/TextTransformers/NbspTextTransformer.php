<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert\TextTransformers;


/**
 * Convert UTF-8 non-breaking spaces back to entities (&nbsp;).
 */
class NbspTextTransformer extends ReplaceTextTransformer {
    public function __construct() {
        $nbsp = html_entity_decode('&nbsp;', ENT_HTML401, 'UTF-8');
        parent::__construct($nbsp, '&nbsp;', 'entities: nbsp');
    }
}
