<?php

namespace RtfmConvert;

class DocLoader {

    public function get($url) {
        return file_get_contents($url);
    }
}