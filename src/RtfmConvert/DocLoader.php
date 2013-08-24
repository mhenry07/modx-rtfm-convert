<?php

namespace RtfmConvert;

class DocLoader {

    /**
     * @param string $url The URL of the web page to get.
     * @param string $filename A local copy of the page to try loading first.
     * @throws RtfmException
     * @return string Returns the contents of the response.
     */
    public function get($url, $filename = null) {
        $source = $url;
        if (!is_null($filename) && file_exists($filename))
            $source = $filename;

        try {
            return file_get_contents($source);
        } catch (\Exception $e) {
            throw new RtfmException($e->getMessage(), $e->getCode(), $e);
        }
    }
}