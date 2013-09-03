<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Infrastructure;


use RtfmConvert\RtfmException;

class FileIo {

    /**
     * @param string $filename
     * @return bool
     */
    public function exists($filename) {
        return file_exists($filename);
    }

    /**
     * @param string $pathname
     * @throws RtfmException
     */
    public function mkdir($pathname) {
        if (!mkdir($pathname, 0777, true))
            throw new RtfmException("Error creating directory: {$pathname}");
    }

    /**
     * @param string $filename
     * @return string
     * @throws RtfmException
     */
    public function read($filename) {
        $contents = file_get_contents($filename);
        if ($contents === false)
            throw new RtfmException("Error reading file: {$filename}");
        return $contents;
    }

    /**
     * @param string $filename
     * @param string $data
     * @throws RtfmException
     */
    public function write($filename, $data) {
        if (file_put_contents($filename, $data) === false)
            throw new RtfmException("Error writing file: {$filename}");
    }

    public function isLocalFile($filename) {
        return preg_match('#^https?\://#', $filename) === 0;
    }
}
