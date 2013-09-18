<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class PathHelper {

    /**
     * Check if the path/url appears to be a local file.
     * @param string $filename
     * @return bool
     */
    public static function isLocalFile($filename) {
        return preg_match('#^https?\://#', $filename) === 0;
    }

    /**
     * Combine two parts of a path, e.g. a base directory and a filename.
     * @param string $part1 The first part of the path
     * @param string $part2 The second part of the path
     * @return string The resulting path
     */
    public static function join($part1, $part2) {
        return preg_replace('#[/\\\\]$#', '', $part1) . '/' .
            preg_replace('#^[/\\\\]#', '', $part2);
    }

    /**
     * Normalize directory separators in a path.
     * @param string $path
     * @return string
     */
    public static function normalize($path) {
        if (DIRECTORY_SEPARATOR == '\\')
            $path = strtr($path, '/', DIRECTORY_SEPARATOR);
        return $path;
    }

    public static function convertRelativeUrlToFilePath($relativeUrl) {
        $specialChars = array('?', '"', '*');
        $replacements = array('/', '%22', '%2A');
        return str_replace($specialChars, $replacements, $relativeUrl);
    }
}
