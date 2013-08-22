<?php

require '../vendor/autoload.php';

use SebastianBergmann\Diff\Differ;

// TODO: generate csv of diff stats

$urlPath = '/display/revolution20/Tag+Syntax';
$tocFile = 'community.html';
$oldBaseUrl = 'http://oldrtfm.modx.com';
$newBaseUrl = 'http://rtfm.modx.com';

$baseDataPath = '../data';

class rtfmData {
    public $urlPath;
    public $title;
    public $localDir;
    public $textDiffStat;
    public $newId;
    public $newUrlPath;
    public $errorMsg;

    public function __construct($urlPath) {
        $this->urlPath = $urlPath;
    }

    public function addError($msg) {
        if (!empty($this->errorMsg))
            $this->errorMsg .= PHP_EOL;
        $this->errorMsg .= $msg;
    }
}

class DiffStat {
    private $added;
    private $deleted;
    private $name;

    public function __construct($diff, $name) {
        $this->name = $name;
        $this->parse($diff);
    }

    protected function parse($diff) {
        $chunks = preg_split('/^@@[^@]+@@$/m', $diff);
        $added = 0;
        $deleted = 0;
        // skip header
        for ($i = 1; $i < count($chunks); $i++) {
            $chunk = $chunks[$i];
            $added += preg_match_all('/^\+/m', $chunk);
            $deleted += preg_match_all('/^\-/m', $chunk);
        }
        $this->added = $added;
        $this->deleted = $deleted;
    }

    public function formatNumstat() {
        return "{$this->added} added\t{$this->deleted} deleted\t{$this->name}";
    }

    public function getAdded() {
        return $this->added;
    }

    public function getDeleted() {
        return $this->deleted;
    }

    public function getName() {
        return $this->name;
    }
}

class RtfmException extends Exception {
}

function stripCarriageReturns($str) {
    return str_replace(chr(13), '', $str);
}

// get all hrefs from a file
// excluding anchors
function getHrefs($filename) {
    $hrefs = array();
    $html = stripCarriageReturns(file_get_contents($filename));

    $doc = htmlqp($html);
    foreach ($doc->find('a') as $link) {
        $href = $link->attr('href');
        if (substr($href, 0, 1) != '#')
            $hrefs[] = $href;
    }
    return $hrefs;
}

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

// fix improperly nested lists
function fixNestedLists($qp) {
    $nestedLists = $qp->find('ol > ol, ol > ul, ul > ol, ul > ul');
    foreach ($nestedLists as $list) {
        $prevLi = $list->prev('li')->branch();
        $list->detach()->attach($prevLi);
    }
}

function getWebPage($baseUrl, $path) {
    $url = $baseUrl . $path;
    $html = file_get_contents($url);
    if ($html === false)
        throw new RtfmException("Error retrieving {$url}");
    return stripCarriageReturns($html);
}

function formatNewPageInfo($fullHtml) {
    $qp = htmlqp($fullHtml, 'body');
    $title = $qp->find('.body-section .content section header h1')->text();
    $pageId = $qp->attr('data-page-id');
    $uri = $qp->attr('data-uri');
    return "New page info:\n\ttitle: {$title}\n\tpage-id: {$pageId}\n\turi: {$uri}\n";
}

function getNewRtfmContent($html) {
    $contentStart = '<!-- start content -->';
    $contentEnd = '<!-- end content -->';
    $tempFile = $GLOBALS['baseDataPath'] . '/temp.new.html';

    $content = getSubstringBetween($html, $contentStart, $contentEnd);
    if ($content === false)
        throw new RtfmException('Error extracting content');

    // htmlqp($content)->writeHTML($tempFile);
    // $content = file_get_contents($tempFile);
    // unlink($tempFile);

    return $content;
}

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

function getContent($html, $newOrOld) {
    if ($newOrOld == 'new') 
        return getNewRtfmContent($html);
    return getOldRtfmContent($html);
}

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

function getTextContent($html) {
    return htmlqp($html)->text();
}

function cleanUpWhitespace($str) {
    $nbsp = mb_convert_encoding('&nbsp;', 'UTF-8', 'HTML-ENTITIES');
    $str = preg_replace('/[' . $nbsp . ']/u', ' ', $str);
    $str = preg_replace('/[ \t]+\n/', "\n", $str);
    $str = preg_replace('/(?<=\S)[ ]{2,}/', ' ', $str);
    $str = preg_replace('/\n{2,}/', "\n", $str);
    return trim($str);
}

function getFilePath($path, $filename) {
    return $GLOBALS['baseDataPath'] . str_replace('/display', '', $path) . '/' . $filename;
}

function getRtfmText($baseUrl, $path, $newOrOld, $useCached, $rtfmData) {
    if (!file_exists($rtfmData->localDir))
        mkdir($$rtfmData->localDir, 0777, true);

    $fullHtmlFilename = getFilePath($path, "full.{$newOrOld}.html");
    if ($useCached && file_exists($fullHtmlFilename)) {
        echo "Loading {$baseUrl}{$path} from cache" . PHP_EOL;
        $fullHtml = file_get_contents($fullHtmlFilename);
    } else {
        echo "Retrieving {$baseUrl}{$path}\n";
        $fullHtml = getWebPage($baseUrl, $path);
        file_put_contents($fullHtmlFilename, $fullHtml);
    }
    if ($newOrOld == 'new')
        echo formatNewPageInfo($fullHtml);

    $content = getContent($fullHtml, $newOrOld);
    file_put_contents(getFilePath($path, "content.{$newOrOld}.html"), $content);

    $tidy = tidyHtml($content);
    file_put_contents(getFilePath($path, "tidy.{$newOrOld}.html"), $tidy);

    $text = getTextContent($tidy);
    $trimmed = cleanUpWhitespace($text);
    file_put_contents(getFilePath($path, "text.{$newOrOld}.txt"), $trimmed);

    return $trimmed;
}

function diff($urlPath, $useCached, $rtfmData) {
    echo "\nGenerating diff for {$urlPath}\n";
    $rtfmData->localDir = getFilePath($urlPath, '');
    echo "Local directory {$rtfmData->localDir}\n";

    $oldText = getRtfmText($GLOBALS['oldBaseUrl'], $urlPath, 'old', $useCached, $rtfmData);
    $newText = getRtfmText($GLOBALS['newBaseUrl'], $urlPath, 'new', $useCached, $rtfmData);

    $differ = new Differ;
    $diff = $differ->diff($oldText, $newText);
    file_put_contents(getFilePath($urlPath, "text.diff"), $diff);

    $rtfmData->textDiffStat = new DiffStat($diff, $urlPath . ' (text)');
    echo $rtfmData->textDiffStat->formatNumstat() . PHP_EOL;

    return $diff;
}

function generateTextDiffsForRtfmSpace($spaceTocFile, $useCached) {
    echo "\nGetting hrefs from {$spaceTocFile}\n\n";
    $rtfmData = array();
    $hrefs = getHrefs($spaceTocFile);
    foreach ($hrefs as $href) {
        $rtfmDatum = new rtfmData($href);
        $rtfmData[]= $rtfmDatum;
        try {
            diff($href, $useCached, $rtfmDatum);
        } catch (RtfmException $e) {
            echo 'Caught exception: ',  $e->getMessage(), PHP_EOL;
            $rtfmDatum->addError($e->getMessage());
        }
    }
}

//diff($urlPath, true);
generateTextDiffsForRtfmSpace($tocFile, true);
