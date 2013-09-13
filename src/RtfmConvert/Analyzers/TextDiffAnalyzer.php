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
use SebastianBergmann\Diff\Differ;

/**
 * Class TextDiffAnalyzer
 * This class is intended for use in conjunction with TextConverter.
 *
 * @package RtfmConvert\Analyzers
 */
class TextDiffAnalyzer implements ProcessorOperationInterface {
    const DIFFER_OLD = 0;
    const DIFFER_ADDED = 1;
    const DIFFER_REMOVED = 2;

    protected $fileIo;
    protected $baseDir;
    protected $name1;
    protected $name2;

    public function __construct(FileIo $fileIo = null) {
        $this->fileIo = $fileIo ? : new FileIo();
    }

    public static function create($name1, $name2, $baseDir,
                                  FileIo $fileIo = null) {
        $analyzer = new TextDiffAnalyzer($fileIo);
        $analyzer->baseDir = $baseDir;
        $analyzer->name1 = $name1;
        $analyzer->name2 = $name2;
        return $analyzer;
    }

    /**
     * @param \RtfmConvert\PageData $pageData
     * @return \RtfmConvert\PageData
     */
    public function process($pageData) {
        $stats = $pageData->getStats();
        $file1 = $stats->getStat(TextConverter::getLabel($this->name1),
            PageStatistics::VALUE);
        $file2 = $stats->getStat(TextConverter::getLabel($this->name2),
            PageStatistics::VALUE);
        $text1 = $this->fileIo->read($file1);
        $text2 = $this->fileIo->read($file2);

        $diffArray = $this->getDiffArray($text1, $text2);
        $diffHeader = $this->getDiffHeader($file1, $file2);
        $diffText = $this->getDiffText($text1, $text2, $diffHeader);
        $diffFile = $this->getDiffFile($pageData);
        $this->writeDiff($diffFile, $diffText);
        $this->addStat($pageData, $diffArray);
        return $pageData;
    }

    protected function getLabel() {
        return 'text diff: ' . $this->name1 . ' ' . $this->name2;
    }

    protected function getDiffFile(PageData $pageData) {
        $stats = $pageData->getStats();
        $pagePath = $stats->getStat(PageStatistics::PATH_LABEL,
            PageStatistics::VALUE);
        $path = PathHelper::join($this->baseDir, $pagePath);
        $filename = $this->name1 . '-' . $this->name2 . '.txt.diff';
        return PathHelper::join($path, $filename);
    }

    protected function getDiffHeader($file1, $file2) {
        $file1 = pathinfo($file1, PATHINFO_BASENAME);
        $file2 = pathinfo($file2, PATHINFO_BASENAME);
        return "--- {$file1}\n+++ {$file2}\n";
    }

    protected function getDiffArray($text1, $text2) {
        $differ = new Differ();
        return $differ->diffToArray($text1, $text2);
    }

    protected function getDiffText($text1, $text2, $header) {
        $differ = new Differ($header);
        return $differ->diff($text1, $text2);
    }

    protected function writeDiff($filename, $diffText) {
        $dir = dirname($filename);
        if (!$this->fileIo->exists($dir))
            $this->fileIo->mkdir($dir);
        $this->fileIo->write($filename, $diffText);
    }

    protected function addStat(PageData $pageData, array $diffArray) {
        $insertions = $this->getInsertions($diffArray);
        $deletions = $this->getDeletions($diffArray);
        $totalChanges = $insertions + $deletions;

        $data = array('insertions (+)' => $insertions,
            'deletions (-)' => $deletions,
            'filename' => PathHelper::normalize($this->getDiffFile($pageData)));
        $pageData->addValueStat($this->getLabel(), $totalChanges,
            array(PageStatistics::DATA => $data));
        if ($totalChanges > 0) {
            $pageData->incrementStat($this->getLabel(), PageStatistics::WARNING,
                $totalChanges,
                "Text content does not match. insertions (+): {$insertions} deletions (-): {$deletions}");
        }
    }

    /**
     * Parse the array returned by Differ::diffToArray()
     *
     * Each array element contains two elements:
     *   - [0] => string $token
     *   - [1] => 2|1|0
     *
     * - 2: REMOVED: $token was removed from $from
     * - 1: ADDED: $token was added to $from
     * - 0: OLD: $token is not changed in $to
     */
    protected function getInsertions(array $diffArray) {
        $insertions = array_filter($diffArray,
            function ($value) {
                return $value[1] === self::DIFFER_ADDED;
            });
        return count($insertions);
    }

    protected function getDeletions(array $diffArray) {
        $deletions = array_filter($diffArray,
            function ($value) {
                return $value[1] === self::DIFFER_REMOVED;
            });
        return count($deletions);
    }
}
