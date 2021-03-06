<?php

namespace RtfmConvert\Infrastructure;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\PathHelper;
use RtfmConvert\RtfmException;

/**
 * Class PageLoader
 * A class for retrieving web pages from a URL or from the local filesystem.
 * @package RtfmConvert.
 */
class PageLoader implements PageLoaderInterface {
    protected $statsPrefix = '';
    protected $curlWrapper;
    protected $fileIo;

    function __construct(CurlWrapper $curlWrapper = null,
                         FileIo $fileIo = null) {
        $this->curlWrapper = $curlWrapper ? : new CurlWrapper();
        $this ->fileIo = $fileIo ? : new FileIo();
    }

    public function setStatsPrefix($prefix) {
        $this->statsPrefix = $prefix;
    }

    /**
     * Get a web page as a string.
     *
     * @param string $url The URL of the web page to retrieve.
     * @param PageStatistics $stats
     * @throws \RtfmConvert\RtfmException
     * @return string Returns the contents of the response.
     */
    public function get($url, PageStatistics $stats = null) {
        if (is_null($stats))
            $stats = new PageStatistics();

        try {
            $contents = $this->getContents($url, $stats);
        } catch (\Exception $e) {
            throw new RtfmException($e->getMessage(), $e->getCode(), $e);
        }
        return $contents;
    }

    /**
     * @param string $url
     * @param PageStatistics $stats
     * @throws \RtfmConvert\RtfmException
     * @return PageData
     */
    public function getData($url, PageStatistics $stats = null) {
        if (is_null($stats))
            $stats = new PageStatistics();
        return new PageData($this->get($url, $stats), $stats);
    }

    private function getContents($url, PageStatistics $stats = null) {
        if (PathHelper::isLocalFile($url))
            return $this->fileIo->read($url);
        return $this->curlGet($url, $stats);
    }

    private function curlGet($url, PageStatistics $stats = null) {
        try {
            $curl = $this->curlWrapper->create($url);
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
            $contentLengthHeader =
                intval($curl->getinfo(CURLINFO_CONTENT_LENGTH_DOWNLOAD));
        } finally {
            $curl->close();
        }

        $options = $httpCode >= 400 || $output === false ?
            array(PageStatistics::ERROR, 1) : array();
        $stats->addValueStat($this->statsPrefix . 'http status code',
            $httpCode, $options);
        if ($output === false)
            throw new RtfmException(
                "Failed to retrieve url (code {$httpCode}): {$url}");

        $downloadBytes = strlen($output);
        $stats->addValueStat($this->statsPrefix . 'content length header',
            $contentLengthHeader);
        $options = $contentLengthHeader > 0 &&
            $downloadBytes != $contentLengthHeader ?
            array(PageStatistics::ERROR, 1) : array();
        $stats->addValueStat($this->statsPrefix . 'bytes', $downloadBytes,
            $options);
        if (!is_null($contentLengthHeader) && $contentLengthHeader > 0 &&
            $downloadBytes != $contentLengthHeader) {
            throw new RtfmException(
                "Bytes downloaded ({$downloadBytes}) does not match Content-Length header ({$contentLengthHeader})");
        }

        return $output;
    }
}
