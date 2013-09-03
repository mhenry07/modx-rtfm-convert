<?php
/**
 * Parse links to oldrtfm.modx.com pages from one or more local table of
 * contents file(s). Returns an array of arrays.
 *
 * TOC files should be located in oldrtfm-toc in the project root.
 *
 * @author: Mike Henry
 */

namespace RtfmConvert;


use RtfmConvert\Infrastructure\FileIo;

class OldRtfmTocParser {
    protected $baseUrl = 'http://oldrtfm.modx.com';
    protected $fileIo;

    function __construct(FileIo $fileIo = null) {
        $this->fileIo = $fileIo ? : new FileIo();
    }

    public function setBaseUrl($baseUrl) {
        $this->baseUrl = $baseUrl;
    }

    public function parseTocFile($filename) {
        $result = array();
        $html = $this->fileIo->read($filename);
        $qp = RtfmQueryPath::htmlqp($html);
        $qp->find('.plugin_pagetree_children_content a')
            ->each(function ($index, $item) use (&$result, $filename) {
                $qp = qp($item);
                $result[] = array(
                    'href' => $qp->attr('href'),
                    'title' => $qp->text(),
                    'url' => $this->baseUrl . $qp->attr('href'),
                    'source' => $filename
                );
            });
        return $result;
    }

    /**
     * @param string $dirname E.g. '../oldrtfm-toc'
     * @return array
     * Returns an array of arrays containing href, title, url & source.
     */
    public function parseTocDirectory($dirname) {
        $result = array();
        $files = $this->fileIo->findPathnames($dirname . '/*.html');
        foreach ($files as $file)
            $result = array_merge($result, $this->parseTocFile($file));
        return $result;
    }
}
