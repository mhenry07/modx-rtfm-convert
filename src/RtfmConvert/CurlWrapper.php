<?php
/**
 * This is a partial wrapper for PHP curl.
 * @author: Mike Henry
 */

namespace RtfmConvert;


class CurlWrapper {
    private $ch = null;

    public function __construct($url = null) {
        $ch = curl_init($url);
        if ($ch === false)
            throw new RtfmException('Error initializing cURL.');
        $this->ch = $ch;
    }

    public function __destruct() {
        if (!is_null($this->ch))
            curl_close($this->ch);
    }

    public function close() {
        curl_close($this->ch);
        $this->ch = null;
    }

    // will curl_errno() return CURLE_PARTIAL_FILE if the download is incomplete?
    public function exec() {
        $result = curl_exec($this->ch);
        $errno = curl_errno($this->ch);
        if ($errno !== 0) {
            $errorMessage = curl_error($this->ch);
            echo "cURL error ({$errno}): {$errorMessage}";
            throw new RtfmException("cURL error ({$errno}): {$errorMessage}");
        }
        return $result;
    }

    public function getinfo($opt = null) {
        return curl_getinfo($this->ch, $opt);
    }

    public function setopt($option, $value) {
        return curl_setopt($this->ch, $option, $value);
    }

    public function setoptArray(array $options) {
        return curl_setopt_array($this->ch, $options);
    }
}