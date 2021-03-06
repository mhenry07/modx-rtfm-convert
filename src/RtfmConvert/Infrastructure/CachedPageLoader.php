<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Infrastructure;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\PathHelper;
use RtfmConvert\RtfmException;

class CachedPageLoader implements PageLoaderInterface {
    /** @var string */
    protected $baseDirectory;
    /** @var string */
    protected $statsPrefix = '';
    /** @var PageLoaderInterface */
    protected $basePageLoader;
    /** @var FileIo */
    protected $fileIo;

    /**
     * @param PageLoaderInterface $basePageLoader
     * @param FileIo $fileIo
     */
    function __construct($basePageLoader = null, $fileIo = null) {
        $this->basePageLoader = $basePageLoader ? : new PageLoader();
        $this->fileIo = $fileIo ? : new FileIo();
    }

    /** @param string $baseDirectory */
    public function setBaseDirectory($baseDirectory) {
        $this->baseDirectory = $baseDirectory;
    }

    public function setStatsPrefix($prefix) {
        $this->statsPrefix = $prefix;
        $this->basePageLoader->setStatsPrefix($prefix);
    }

    function get($url, PageStatistics $stats = null) {
        $fileIo = $this->fileIo;
        if (is_null($stats))
            $stats = new PageStatistics();
        if (PathHelper::isLocalFile($url))
            return $this->basePageLoader->get($url, $stats);

        $cacheFile = $this->getCachePath($url);
        if ($fileIo->exists($cacheFile)) {
            $stats->addValueStat($this->statsPrefix . 'cache: loaded from',
                PathHelper::normalize($cacheFile));
            $contents = $fileIo->read($cacheFile);
            $stats->addValueStat($this->statsPrefix . 'bytes',
                strlen($contents));
            return $contents;
        }
        $contents = $this->basePageLoader->get($url, $stats);
        if (!$fileIo->exists(dirname($cacheFile))) {
            $stats->addValueStat($this->statsPrefix . 'cache: saved to',
                PathHelper::normalize($cacheFile));
            $fileIo->mkdir(dirname($cacheFile));
        }
        $fileIo->write($cacheFile, $contents);
        return $contents;
    }

    function getData($url, PageStatistics $stats = null) {
        if (is_null($stats))
            $stats = new PageStatistics();
        return new PageData($this->get($url, $stats), $stats);
    }

    public function getCachePath($url) {
        $count = preg_match('!^https?://([^#]*)!', $url, $matches);
        if ($count === 0 || $count === false)
            throw new RtfmException("Error parsing URL: {$url}");

        $urlPath = parse_url($url, PHP_URL_PATH);
        $path = trim(parse_url($url, PHP_URL_HOST) . $urlPath, '/');
        $hasValidExtension = false;
        if ($urlPath) {
            $validExtensions = array('css', 'gif', 'htm', 'html', 'jpg', 'js', 'png', 'txt');
            $ext = pathinfo($urlPath, PATHINFO_EXTENSION);
            foreach ($validExtensions as $validExt) {
                if (strtolower($ext) === $validExt) {
                    $hasValidExtension = true;
                    break;
                }
            }
        }
        // include query string and .html extension in paths without a valid extension
        if (!$hasValidExtension) {
            $urlQuery = parse_url($url, PHP_URL_QUERY);
            if ($urlQuery) {
                if (strlen($urlQuery) > 150)
                    $urlQuery = sha1($urlQuery);
                $path .= '/' . $urlQuery;
            }
            $path .= '.html';
        }
        return PathHelper::join($this->baseDirectory, $path);
    }
}
