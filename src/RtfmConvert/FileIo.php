<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class FileIo {

    /**
     * @param string $filename
     * @param PageData $pageData
     * @throws RtfmException
     */
    public function write($filename, $pageData) {
        if (file_put_contents($filename, $pageData->getHtmlString()) === false)
            throw new RtfmException("Error writing file: {$filename}");
    }
}
