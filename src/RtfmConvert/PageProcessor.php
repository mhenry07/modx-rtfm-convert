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
    protected $statsPrefix = '';

    const OUTPUT_FILE_LABEL = 'output: file';

    public function __construct(PageLoaderInterface $pageLoader = null, FileIo $fileIo = null) {
        $this->pageLoader = $pageLoader ? : new CachedPageLoader();
        $this->fileIo = $fileIo ? : new FileIo();
    }

    public function setStatsPrefix($prefix) {
        $this->statsPrefix = $prefix;
    }

    public function processPage($source, $dest, $stats = null, $saveStats = true) {
        $startTime = microtime(true);
        echo 'Processing: ', $source, PHP_EOL;
        if (is_null($stats))
            $stats = new PageStatistics();
        $stats->addValueStat(
            $this->formatLabel(PageStatistics::SOURCE_URL_LABEL), $source);
        $stats->addValueStat($this->statsPrefix . 'time: start',
            date(DATE_W3C));
        try {
            $this->pageLoader->setStatsPrefix($this->statsPrefix ? : 'source: ');
            $pageData = $this->pageLoader->getData($source, $stats);

            /** @var ProcessorOperationInterface $operation */
            foreach ($this->operations as $operation)
                $pageData = $operation->process($pageData);

            $this->savePage($dest, $pageData);
        } catch (\Exception $e) {
            echo $e->getMessage();
            if (!isset($pageData))
                $pageData = new PageData(null, $stats);
            $pageData->addValueStat($this->statsPrefix . 'Errors', null,
                array(PageStatistics::ERROR => 1,
                    PageStatistics::ERROR_MESSAGES => $e->getMessage()));
        }

        $elapsedTime = microtime(true) - $startTime;
        $pageData->addValueStat($this->statsPrefix . 'time: elapsed (s)',
            $elapsedTime);

        if ($saveStats)
            $this->saveStats($dest, $pageData);
        $this->printSummary($pageData);
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

        $pageData->addValueStat($this->statsPrefix . self::OUTPUT_FILE_LABEL,
            PathHelper::normalize($dest));
        $pageData->addValueStat($this->statsPrefix . 'output: bytes',
            strlen($html));
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

    protected function printSummary(PageData $pageData) {
        $stats = $pageData->getStats();
        if (is_null($stats))
            return;
        $statsArray = $stats->getStats();
        $errors = PageStatistics::countErrors($statsArray);
        $errorString = $errors ? "Errors: {$errors} " : '';
        $warnings = PageStatistics::countWarnings($statsArray);
        $warningString = $warnings ? "Warnings: {$warnings} " : '';
        $file = $stats->getStat($this->statsPrefix . self::OUTPUT_FILE_LABEL,
            PageStatistics::VALUE);
        $fileString = $file ? "Saved to: {$file}" : '';

        echo '  ', $errorString, $warningString, $fileString, PHP_EOL;
    }

    protected function formatLabel($label) {
        if ($this->statsPrefix == '' || $this->statsPrefix == 'source: ')
            return $label;
        return str_replace('source: ', $this->statsPrefix, $label);
    }
}
