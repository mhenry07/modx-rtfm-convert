<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\Infrastructure\FileIo;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;
use RtfmConvert\PathHelper;
use RtfmConvert\ProcessorOperationInterface;
use tidy;

class TextConverter implements ProcessorOperationInterface {
    protected $fileIo;
    protected $basePath;
    protected $name;

    public static function create($name, $basePath, FileIo $fileIo = null) {
        $converter = new TextConverter($fileIo);
        $converter->setName($name);
        $converter->setBasePath($basePath);
        return $converter;
    }

    public function __construct(FileIo $fileIo = null) {
        $this->fileIo = $fileIo ? : new FileIo();
    }

    public function setBasePath($path) {
        $this->basePath = $path;
    }

    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $statsArray = $pageData->getStats()->getStats();
        $pagePath = $statsArray[PageStatistics::PATH_LABEL][PageStatistics::VALUE];
        $path = PathHelper::join($this->basePath, $pagePath);
        if (!$this->fileIo->exists($path))
            $this->fileIo->mkdir($path);
        $file = PathHelper::join($path, $this->name . '.txt');

        $this->fileIo->write($file, $this->convertToText($pageData));

        $pageData->addValueStat($this->getLabel(),
            PathHelper::normalize($file));
        return $pageData;
    }

    /**
     * @param PageData $pageData
     * @return string
     */
    protected function convertToText(PageData $pageData) {
        $html = $pageData->getHtmlDocument();
        $html = $this->tidyHtml($html);
        $text = htmlqp($html)->text();
        $text = $this->cleanUpWhitespace($text);
        return $text;
    }

    protected function tidyHtml($html) {
        $config = array(
            'output-xhtml' => true,
            'show-body-only' => true,
            'break-before-br' => true,
            'indent' => false,
            'vertical-space' => true,
            'wrap' => 0,
            'char-encoding' => 'utf8',
            'newline' => 'LF',
            'output-bom' => false);
        $tidy = new tidy();
        return $tidy->repairString($html, $config, 'utf8');
    }

    /**
     * @param string $str
     * @return string
     */
    protected function cleanUpWhitespace($str) {
//        $nbsp = mb_convert_encoding('&nbsp;', 'UTF-8', 'HTML-ENTITIES');
//        $str = preg_replace('/[' . $nbsp . ']/u', ' ', $str);
        $str = preg_replace('/[ \t]+\n/', "\n", $str);
        $str = preg_replace('/(?<=\S)[ ]{2,}/', ' ', $str);
        $str = preg_replace('/\n{2,}/', "\n", $str);
        return trim($str);
    }

    protected function getLabel() {
        return $this->name . ': text file';
    }
}
