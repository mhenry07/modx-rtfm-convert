<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class FileIo {

    /**
     * @param string $filename
     * @param string $data
     * @throws RtfmException
     */
    public function write($filename, $data) {
        if (file_put_contents($filename, $data) === false)
            throw new RtfmException("Error writing file: {$filename}");
    }
}
