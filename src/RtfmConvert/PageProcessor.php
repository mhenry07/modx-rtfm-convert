<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\Infrastructure\CachedPageLoader;
use RtfmConvert\Infrastructure\FileIo;
use RtfmConvert\Infrastructure\PageLoaderInterface;

class PageProcessor {
    protected $pageLoader;
    protected $fileIo;
    protected $operations = array();

    function __construct(PageLoaderInterface $pageLoader = null, FileIo $fileIo = null) {
        $this->pageLoader = $pageLoader ? : new CachedPageLoader();
        $this->fileIo = $fileIo ? : new FileIo();
    }

    public function processPage($source, $dest, $saveStats = true) {
        echo 'Processing: ', $source, PHP_EOL;
        $stats = new PageStatistics();
        $stats->addValueStat('source: url', $source);
        $pageData = $this->pageLoader->getData($source, $stats);

        /** @var ProcessorOperationInterface $operation */
        foreach ($this->operations as $operation)
            $pageData = $operation->process($pageData);

        $this->savePage($dest, $pageData);
        if ($saveStats)
            $this->saveStats($dest, $pageData);
        return $pageData;
    }

    /**
     * Registers an operation for the processPage pipeline.
     * Operations will be executed in the order registered.
     * @param ProcessorOperationInterface $operation
     */
    public function register(ProcessorOperationInterface $operation) {
        $this->operations[] = $operation;
    }

    /**
     * @param string $dest
     * @param PageData $pageData
     */
    protected function savePage($dest, PageData $pageData) {
        if (!$this->fileIo->exists(dirname($dest)))
            $this->fileIo->mkdir(dirname($dest));
        $html = $pageData->getHtmlString();
        $this->fileIo->write($dest, $html);
    }

    /**
     * @param string $dest
     * @param PageData $pageData
     */
    protected function saveStats($dest, PageData $pageData) {
        if (is_null($pageData->getStats()))
            return;
        $json = json_encode($pageData->getStats()->getStats());
        $this->fileIo->write("{$dest}.json", $json);
    }
}
