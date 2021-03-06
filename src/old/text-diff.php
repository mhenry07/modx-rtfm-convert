<?php

/**
 * text-diff.php attempts to process all pages from oldrtfm.modx.com and
 * rtfm.modx.com, get contents, get some info and statistics, extract and
 * normalize the text so it can do a diff between the old and new page, and
 * exports a bunch of info to a .csv.
 */

// Note: it took ~1:40 (one hour and forty minutes) to run the script all the way through the first time
// With all the pages cached, it only took about 4 minutes.

// TODO: refactor RtfmData into RtfmSiteData['old'] and RtfmSiteData['new']

require '../vendor/autoload.php';

use SebastianBergmann\Diff\Differ;

//$urlPath = '/display/revolution20/Tag+Syntax';
$oldBaseUrl = 'http://oldrtfm.modx.com';
$newBaseUrl = 'http://rtfm.modx.com';

$baseDataPath = '../data';
$csvFile = $baseDataPath . '/data.csv';
$tocDir = '../oldrtfm-toc';
//$tocFile = $tocDir . '/community.html';

class RtfmData {
    public $title;
    public $spaceKey;
    public $newId;
    public $urlPath;
    public $newUrlPath;
    public $newTextLineCount;
    public $oldTextLineCount;
    public $newPreElementCount;
    public $oldPreElementCount;
    public $newLastEditDate;
    public $localDir;
    public $errorMsg;
    private $textDiffStat;

    /** @param string $urlPath */
    public function __construct($urlPath) {
        $this->urlPath = $urlPath;
    }

    /** @return DiffStat */
    public function getTextDiffStat() {
        if (empty($this->textDiffStat))
            $this->textDiffStat = new DiffStat('', '');
        return $this->textDiffStat;
    }

    /** @param DiffStat $diffStat */
    public function setTextDiffStat(DiffStat $diffStat) {
        $this->textDiffStat = $diffStat;
    }

    /** @param string $msg */
    public function addError($msg) {
        if (!empty($this->errorMsg))
            $this->errorMsg .= '; ';
        $this->errorMsg .= $msg;
    }

    /** @return string */
    public function getOldUrl() {
        return $GLOBALS['oldBaseUrl'] . $this->urlPath;
    }

    /** @return string */
    public function getNewUrl() {
        if (empty($this->newUrlPath))
            return '';
        return $GLOBALS['newBaseUrl'] . $this->newUrlPath;
    }

    /** @return int|string */
    public function getMissingPreCount() {
        if (isset($this->oldPreElementCount) &&
            isset($this->newPreElementCount))
            return $this->oldPreElementCount - $this->newPreElementCount;
        return '';
    }
}

class DiffStat {
    private $insertions;
    private $deletions;
    private $name;

    /**
     * @param string $diff
     * @param string $name
     */
    public function __construct($diff, $name) {
        $this->name = $name;
        $this->parse($diff);
    }

    protected function parse($diff) {
        if (empty($diff))
            return;
        $chunks = preg_split('/^@@[^@]+@@$/m', $diff);
        $insertions = 0;
        $deletions = 0;
        // skip header
        for ($i = 1; $i < count($chunks); $i++) {
            $chunk = $chunks[$i];
            $insertions += preg_match_all('/^\+/m', $chunk);
            $deletions += preg_match_all('/^\-/m', $chunk);
        }
        $this->insertions = $insertions;
        $this->deletions = $deletions;
    }

    /** @return string */
    public function formatNumstat() {
        return "{$this->insertions} insertions(+), {$this->deletions} deletions(-), {$this->name}";
    }

    /** @return int */
    public function getInsertions() {
        return $this->insertions;
    }

    /** @return int */
    public function getDeletions() {
        return $this->deletions;
    }

    /** @return string */
    public function getName() {
        return $this->name;
    }
}

class RtfmDataCsv {
    private $filename;
    private $handle;

    /** @param string $filename */
    public function __construct($filename) {
        $this->filename = $filename;
    }

    /** @param RtfmData[] $rtfmDataArray */
    public function writeCsv(array $rtfmDataArray) {
        echo "\nwriting data to {$this->filename}\n";
        $this->writeHeader();
        foreach ($rtfmDataArray as $rtfmDataItem)
            $this->writeRow($rtfmDataItem);
        $this->end();
    }

    protected function writeHeader() {
        $headings = array('Title', 'Space Key', 'New ID', 'Path', 'Old Url',
            'New Url', 'Insertions(+)', 'Deletions(-)', 'Old # Lines',
            'New # Lines', 'Old # Pre Blocks', 'New # Pre Blocks',
            '# Missing Pre Blocks', 'New Last Edit Date', 'Errors',
            'Local Directory');
        $this->handle = fopen($this->filename, 'w');
        if ($this->handle === false) {
            $last_error = error_get_last();
            throw new RtfmException($last_error['message']);
        }
        fputcsv($this->handle, $headings);
    }

    protected function writeRow(RtfmData $rtfmDataItem) {
        $d = $rtfmDataItem;
        $stat = $d->getTextDiffStat();
        $fields = array($d->title, $d->spaceKey, $d->newId, $d->urlPath,
            $d->getOldUrl(), $d->getNewUrl(), $stat->getInsertions(),
            $stat->getDeletions(), $d->oldTextLineCount, $d->newTextLineCount,
            $d->oldPreElementCount, $d->newPreElementCount,
            $d->getMissingPreCount(), $d->newLastEditDate,
            $d->errorMsg, realpath($d->localDir));
        fputcsv($this->handle, $fields);
    }

    protected function end() {
        if (!is_null($this->handle))
            fclose($this->handle);
        $this->handle = null;
    }
}

class RtfmException extends Exception {
}

/**
 * @param string $str
 * @return string
 */
function stripCarriageReturns($str) {
    return str_replace(chr(13), '', $str);
}

/**
 * @param string $tocDir
 * @return string[]
 */
function getRtfmTocFiles($tocDir) {
    return glob($tocDir . '/*.html');
}

/**
 * get all hrefs from a MODX oldrtfm table of contents file
 * excluding anchors
 * @param string $tocFile
 * @return string[]
 */
function getTocHrefs($tocFile) {
    $hrefs = array();
    $html = stripCarriageReturns(file_get_contents($tocFile));

    /** @var \QueryPath\DOMQuery $link */
    $doc = htmlqp($html);
    foreach ($doc->find('.plugin_pagetree_children_span a') as $link) {
        $href = $link->attr('href');
        if (substr($href, 0, 1) != '#')
            $hrefs[] = $href;
    }
    return $hrefs;
}

/**
 * @param string $str
 * @param string $startStr
 * @param string $endStr
 * @return string|bool
 */
function getSubstringBetween($str, $startStr, $endStr) {
    $startPos = strpos($str, $startStr);
    if ($startPos === false)
        return false;
    $startPos += strlen($startStr);

    $endPos = strpos($str, $endStr, $startPos);
    if ($endPos === false)
        return false;

    $len = $endPos - $startPos;
    return substr($str, $startPos, $len);
}

/**
 * fix improperly nested lists
 * @param \QueryPath\DOMQuery $qp
 */
function fixNestedLists(\QueryPath\DOMQuery $qp) {
    /** @var \QueryPath\DOMQuery $list */
    $nestedLists = $qp->find('ol > ol, ol > ul, ul > ol, ul > ul');
    foreach ($nestedLists as $list) {
        $prevLi = $list->prev('li')->branch();
        $list->detach()->attach($prevLi);
    }
}

/**
 * @param string $baseUrl
 * @param string $path
 * @return string
 * @throws RtfmException
 */
function getWebPage($baseUrl, $path) {
    $url = $baseUrl . $path;
    $html = file_get_contents($url);
    if ($html === false) {
        $last_error = error_get_last();
        throw new RtfmException($last_error['message']);
    }
    return stripCarriageReturns($html);
}

/**
 * Format: <h5>Last edited by {name} on {MMM D, YYYY}. </h5>
 * @param \QueryPath\DOMQuery $newQp
 * @return string
 */
function parseLastEditDate(\QueryPath\DOMQuery $newQp) {
    $text = $newQp->find('.body-section .content section header h5')->text();
    $pattern = '/Last edited by .* on \s*\b(.*)\b\s*\.\s*$/';
    if (preg_match($pattern, $text, $matches) === 1)
        return $matches[1];
    return '';
}

/**
 * @param string $fullHtml
 * @param RtfmData $rtfmData
 */
function parseOldPageInfo($fullHtml, RtfmData $rtfmData) {
    $qp = htmlqp($fullHtml);
    $rtfmData->title = trim($qp->find('#title-text')->text());
    $rtfmData->spaceKey = $qp->find('#confluence-space-key')->attr('content');
}

/**
 * @param string $fullHtml
 * @param $rtfmData
 */
function parseNewPageInfo($fullHtml, RtfmData $rtfmData) {
    $qp = htmlqp($fullHtml, 'body');
    $rtfmData->title = $qp->find('.body-section .content section header h1')->text();
    $rtfmData->newId = $qp->attr('data-page-id');
    $rtfmData->newUrlPath = $qp->attr('data-uri');
    $lastEditDate = parseLastEditDate($qp);
    if ($lastEditDate !== '')
        $rtfmData->newLastEditDate = $lastEditDate;
    echo "New page info:\n\ttitle: {$rtfmData->title}\n\tpage-id: {$rtfmData->newId}\n\turi: {$rtfmData->newUrlPath}\n";
}

/**
 * @param string $fullHtml
 * @param RtfmData $rtfmData
 * @param string $newOrOld
 */
function parsePageInfo($fullHtml, RtfmData $rtfmData, $newOrOld) {
    if ($newOrOld == 'new') {
        parseNewPageInfo($fullHtml, $rtfmData);
    } else {
        parseOldPageInfo($fullHtml, $rtfmData);
    }
}

/**
 * @param string $content
 * @param RtfmData $rtfmData
 * @param string $newOrOld
 * @return int
 */
function getPreBlockCount($content, RtfmData $rtfmData, $newOrOld) {
    $count = htmlqp($content)->find('pre')->count();
    if ($newOrOld == 'new') {
        $rtfmData->newPreElementCount = $count;
    } else {
        $rtfmData->oldPreElementCount = $count;
    }
    return $count;
}

/**
 * @param string $html
 * @return string|bool
 * @throws RtfmException
 */
function getNewRtfmContent($html) {
    $contentStart = '<!-- start content -->';
    $contentEnd = '<!-- end content -->';

    $content = getSubstringBetween($html, $contentStart, $contentEnd);
    if ($content === false)
        throw new RtfmException('Error extracting content');

    // htmlqp($content)->writeHTML($tempFile);
    // $content = file_get_contents($tempFile);
    // unlink($tempFile);

    return $content;
}

/**
 * @param string $html
 * @return string|bool
 */
function getOldRtfmContent($html) {
    $tempFile = $GLOBALS['baseDataPath'] . '/temp.old.html';

    $qp = htmlqp($html, 'div.wiki-content');
    $qp->find('script')->remove();
    $qp->find('style')->remove();
    $qp->find('div.Scrollbar')->remove();

    htmlqp($qp->innerHTML())->writeHTML($tempFile);
    $content = file_get_contents($tempFile);
    unlink($tempFile);
    return $content;
}

/**
 * @param string $html
 * @param string $newOrOld
 * @return string|bool
 */
function getContent($html, $newOrOld) {
    if ($newOrOld == 'new')
        return getNewRtfmContent($html);
    return getOldRtfmContent($html);
}

/**
 * @param string $html
 * @return string
 */
function tidyHtml($html) {
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
 * @param string $html
 * @return mixed
 */
function getTextContent($html) {
    return htmlqp($html)->text();
}

/**
 * @param string $str
 * @return string
 */
function cleanUpWhitespace($str) {
    $nbsp = mb_convert_encoding('&nbsp;', 'UTF-8', 'HTML-ENTITIES');
    $str = preg_replace('/[' . $nbsp . ']/u', ' ', $str);
    $str = preg_replace('/[ \t]+\n/', "\n", $str);
    $str = preg_replace('/(?<=\S)[ ]{2,}/', ' ', $str);
    $str = preg_replace('/\n{2,}/', "\n", $str);
    return trim($str);
}

/**
 * @param string $path
 * @param string $filename
 * @return string
 */
function getFilePath($path, $filename) {
    $dir = str_replace('/display', '', $path);
    if (preg_match('@/pages/viewpage\.action\?pageId=(\d+)@', $path, $matches) === 1)
        $dir = '/pageId/' . $matches[1];
    return $GLOBALS['baseDataPath'] . $dir . '/' . $filename;
}

/**
 * @param string $baseUrl
 * @param string $path
 * @param string $newOrOld
 * @param bool $useCached
 * @param RtfmData $rtfmData
 * @return string
 * @throws RtfmException
 */
function getRtfmText($baseUrl, $path, $newOrOld, $useCached, RtfmData $rtfmData) {
    $localDir = $rtfmData->localDir;
    if (!file_exists($localDir)) {
        if (mkdir($localDir, 0777, true) === false) {
            $last_error = error_get_last();
            throw new RtfmException($last_error['message']);
        }
    }

    $fullHtmlFilename = getFilePath($path, "original.{$newOrOld}.html");
    if ($useCached && file_exists($fullHtmlFilename)) {
        echo "Loading {$baseUrl}{$path} from cache" . PHP_EOL;
        $fullHtml = file_get_contents($fullHtmlFilename);
    } else {
        echo "Retrieving {$baseUrl}{$path}\n";
        $fullHtml = getWebPage($baseUrl, $path);
        file_put_contents($fullHtmlFilename, $fullHtml);
    }
    parsePageInfo($fullHtml, $rtfmData, $newOrOld);

    $content = getContent($fullHtml, $newOrOld);
    $tidy = tidyHtml($content);
    file_put_contents(getFilePath($path, "content.{$newOrOld}.html"), $tidy);
    getPreBlockCount($tidy, $rtfmData, $newOrOld);

    $text = getTextContent($tidy);
    $trimmed = cleanUpWhitespace($text);
    file_put_contents(getFilePath($path, "content.{$newOrOld}.txt"), $trimmed);

    return $trimmed;
}

/**
 * @param string $str
 * @return int
 */
function calcLineCount($str) {
    if ($str === '')
        return 0;
    return substr_count($str, "\n") + 1;
}

/**
 * @param string $urlPath
 * @param bool $useCached
 * @param RtfmData $rtfmData
 * @return string
 */
function diff($urlPath, $useCached, RtfmData $rtfmData) {
    echo "\nGenerating diff for {$urlPath}\n";
    $rtfmData->localDir = getFilePath($urlPath, '');
    echo "Local directory {$rtfmData->localDir}\n";

    $oldText = getRtfmText($GLOBALS['oldBaseUrl'], $urlPath, 'old', $useCached, $rtfmData);
    $rtfmData->oldTextLineCount = calcLineCount($oldText);

    $newText = getRtfmText($GLOBALS['newBaseUrl'], $urlPath, 'new', $useCached, $rtfmData);
    $rtfmData->newTextLineCount = calcLineCount($newText);

    $differ = new Differ;
    $diff = $differ->diff($oldText, $newText);
    file_put_contents(getFilePath($urlPath, "text.diff"), $diff);

    $diffStat = new DiffStat($diff, $urlPath . ' (text)');
    $rtfmData->setTextDiffStat($diffStat);
    echo $diffStat->formatNumstat() . PHP_EOL;

    return $diff;
}

/**
 * @param string $tocFile
 * @param bool $useCached
 * @param RtfmData[] $rtfmDataArray
 */
function generateTextDiffsForRtfmSpace($tocFile, $useCached, array &$rtfmDataArray) {
    echo "\nGetting hrefs from {$tocFile}\n\n";
    $hrefs = getTocHrefs($tocFile);
    foreach ($hrefs as $href) {
        $rtfmDataItem = new RtfmData($href);
        $rtfmDataArray[]= $rtfmDataItem;
        try {
            diff($href, $useCached, $rtfmDataItem);
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
            $rtfmDataItem->addError($e->getMessage());
        }
    }
}

/**
 * @param string $tocDir
 * @param bool $useCached
 * @return RtfmData[]
 */
function generateTextDiffsForAllSpaces($tocDir, $useCached) {
    $rtfmDataArray = array();
    foreach (getRtfmTocFiles($tocDir) as $tocFile)
        generateTextDiffsForRtfmSpace($tocFile, $useCached, $rtfmDataArray);
    return $rtfmDataArray;
}

$startTime = microtime(true);

// make sure csv file is writable before starting
if (($fp = fopen($csvFile, 'w')) === false)
    exit(1);
fclose($fp);

/** @var RtfmData[] $rtfmData */
//diff($urlPath, true);
//$rtfmData = array();
//generateTextDiffsForRtfmSpace($tocFile, true, $rtfmData);
$rtfmData = generateTextDiffsForAllSpaces($tocDir, true);
$csv = new RtfmDataCsv($csvFile);
$csv->writeCsv($rtfmData);

$totalTime = microtime(true) - $startTime;
$minutes = number_format($totalTime / 60, 3);
echo "\nExecution time: {$minutes} minutes\n";
