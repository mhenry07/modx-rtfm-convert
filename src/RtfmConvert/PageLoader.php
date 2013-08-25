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
     * If $cacheFile is specified, try to load it first if it exists.
     * If $cacheFile does not exist, it will fall back to loading $url
     * and a local copy of the page will be written to $cacheFile.
     *
     * @param string $url The URL of the web page to retrieve.
     * @param string|null $cacheFile
     * The filename of a locally cached copy of the page.
     * @throws RtfmException
     * @return string Returns the contents of the response.
     */
    public function get($url, $cacheFile = null) {
        $source = $url;
        if (!is_null($cacheFile) && file_exists($cacheFile))
            $source = $cacheFile;

        try {
            $contents = file_get_contents($source);
            if ($source == $url && !is_null($cacheFile) && $contents !== false)
                file_put_contents($cacheFile, $contents);
        } catch (\Exception $e) {
            throw new RtfmException($e->getMessage(), $e->getCode(), $e);
        }
        return $contents;
    }
}
