<?php

namespace RtfmConvert;

/**
 * Class PageLoader
 * A class for retrieving web pages from a URL or from the local filesystem.
 * @package RtfmConvert
 */
class PageLoader {

    /**
     * Get a web page as a string.
     * If $source1 is a file and does not exist, try $source2.
     * Note: If two sources are specified, it only tries $source2 if $source1
     * is a file that doesn't exist. If $source1 is a URL, it doesn't try
     * $source2 even if $source1 fails.
     *
     * @param string $source1 The primary source of the web page (a URL or filename).
     * @param string $source2 The secondary source of the web page (a URL or filename).
     * @throws RtfmException
     * @return string Returns the contents of the response.
     */
    public function get($source1, $source2 = null) {
        $source = $source1;
        if (!$this->isWebUrl($source1) && !file_exists($source1))
            $source = $source2;

        try {
            return file_get_contents($source);
        } catch (\Exception $e) {
            throw new RtfmException($e->getMessage(), $e->getCode(), $e);
        }
    }

    private function isWebUrl($source1) {
        return preg_match('#^https?\://#', $source1) === 1;
    }
}
