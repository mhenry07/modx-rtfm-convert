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
use RtfmConvert\RtfmQueryPath;
use tidy;

/**
 * Class TextConverter
 * This class is intended for use in conjunction with TextDiffAnalyzer.
 *
 * @package RtfmConvert\Analyzers
 */
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

    public static function getLabel($name) {
        return $name . ': text file';
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
        $stats = $pageData->getStats();
        $pageUrlPath = $stats->getStat(PageStatistics::PATH_LABEL,
            PageStatistics::VALUE);
        $pageFilePath = PathHelper::convertRelativeUrlToFilePath($pageUrlPath);
        $path = PathHelper::join($this->basePath, $pageFilePath);
        if (!$this->fileIo->exists($path))
            $this->fileIo->mkdir($path);
        $file = PathHelper::join($path, $this->name . '.txt');

        $text = $this->convertToText($pageData);
        $this->fileIo->write($file, $text);

        $data = array('lines' => $this->countLines($text),
            'bytes' => strlen($text));
        $pageData->addValueStat(self::getLabel($this->name),
            PathHelper::normalize($file), array(PageStatistics::DATA => $data));
        return $pageData;
    }

    /**
     * @param PageData $pageData
     * @return string
     */
    protected function convertToText(PageData $pageData) {
        $html = $pageData->getHtmlDocument();
        $html = $this->removePagetree($html);
        $html = $this->tidyHtml($html);
        $text = htmlqp($html)->text();
        $text = $this->cleanUpWhitespace($text);
        return $text;
    }

    // pagetrees are common and make it hard to detect other issues if they are included in diffs
    protected function removePagetree($html) {
        $qp = RtfmQueryPath::htmlqp($html);
        $qp->find('div.plugin_pagetree, ul.page-toc, ul.see-also')->remove();
        return RtfmQueryPath::getHtmlString($qp->document());
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

    protected function countLines($text) {
        if ($text === '')
            return 0;
        return substr_count($text, "\n") + 1;
    }
}
