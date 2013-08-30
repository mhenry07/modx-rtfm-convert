<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class PageProcessor {
    protected $pageLoader;
    protected $fileIo;
    protected $operations = array();

    function __construct(PageLoader $pageLoader = null, FileIo $fileIo = null) {
        $this->pageLoader = $pageLoader ? : new PageLoader();
        $this->fileIo = $fileIo ? : new FileIo();
    }

    public function processPage($source, $dest) {
        $pageData = $this->pageLoader->getData($source);

        /** @var ProcessorOperationInterface $operation */
        foreach ($this->operations as $operation)
            $pageData = $operation->process($pageData);

        $this->fileIo->write($dest, $pageData);
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
}
