<?php

namespace RtfmConvert;

class DocLoader {

    /**
     * @param string $url The URL of the web page to get.
     * @throws RtfmException
     * @return string Returns the contents of the response.
     */
    public function get($url) {
        try {
            return file_get_contents($url);
        } catch (\Exception $e) {
            throw new RtfmException($e->getMessage(), $e->getCode(), $e);
        }
    }
}