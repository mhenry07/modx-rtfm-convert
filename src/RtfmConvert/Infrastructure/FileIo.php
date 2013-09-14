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

    public function findPathnames($pattern) {
        $result = glob($pattern);
        if ($result === false)
            throw new RtfmException("Error finding pathnames matching: {$pattern}");
        return $result;
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
     * @return int
     * @throws RtfmException
     */
    public function write($filename, $data) {
        $bytes = file_put_contents($filename, $data);
        if ($bytes === false)
            throw new RtfmException("Error writing file: {$filename}");
        return $bytes;
    }
}
