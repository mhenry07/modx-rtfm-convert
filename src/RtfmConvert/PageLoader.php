<?php

namespace RtfmConvert;

/**
 * Class PageLoader
 * A class for retrieving web pages from a URL or from the local filesystem.
 * @package RtfmConvert.
 * Note: at the moment, it can only handle one request per instance.
 */
class PageLoader {
    private $curlWrapper;
    private $stats;

    function __construct(CurlWrapper $curlWrapper = null, PageStatistics $stats = null) {
        if (!is_null($curlWrapper)) {
            $this->curlWrapper = $curlWrapper;
        } else {
            $this->curlWrapper = new CurlWrapper();
        }
        $this->stats = $stats;
    }

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
        $this->addStat('url', $url);
        $source = $url;
        if (!is_null($cacheFile) && file_exists($cacheFile)) {
            $source = $cacheFile;
            $this->addStat('using cache', true);
        }

        try {
            $contents = $this->getContents($source);
            if ($source == $url && !is_null($cacheFile) && $contents !== false)
                $this->putContents($cacheFile, $contents);
        } catch (\Exception $e) {
            throw new RtfmException($e->getMessage(), $e->getCode(), $e);
        }
        return $contents;
    }

    private function getContents($source) {
        if (!$this->isWebUrl($source))
            return file_get_contents($source);
        return $this->curlGet($source);
    }

    private function putContents($dest, $data) {
        return file_put_contents($dest, $data);
    }

    private function isWebUrl($source) {
        return preg_match('#^https?\://#', $source) === 1;
    }

    private function curlGet($url) {
        $curl = $this->curlWrapper;
        $curl->setoptArray(array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FAILONERROR => true,
            CURLOPT_AUTOREFERER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HEADER => false
        ));
        $output = $curl->exec();
        $httpCode = $curl->getinfo(CURLINFO_HTTP_CODE);
        $contentLengthHeader = intval($curl->getinfo(CURLINFO_CONTENT_LENGTH_DOWNLOAD));
        $curl->close();

        $this->addStat('http status code', $httpCode,
            $httpCode >= 400 || $output === false);
        if ($output === false)
            throw new RtfmException("Failed to retrieve url (code {$httpCode}): {$url}");

        $downloadBytes = strlen($output);
        $this->addStat('content length header', $contentLengthHeader);
        $this->addStat('downloaded bytes', $downloadBytes,
            $contentLengthHeader > 0 && $downloadBytes != $contentLengthHeader);
        if (!is_null($contentLengthHeader) && $contentLengthHeader > 0 &&
            $downloadBytes != $contentLengthHeader) {
            throw new RtfmException("Bytes downloaded ({$downloadBytes}) does not match Content-Length header ({$contentLengthHeader})");
        }

        return $output;
    }

    private function addStat($label, $value, $isWarning = false) {
        if (!is_null($this->stats))
            $this->stats->add($label, $value, false, $isWarning);
    }
}
