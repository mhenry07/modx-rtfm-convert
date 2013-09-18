<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert;


class PathHelperTest extends \PHPUnit_Framework_TestCase {
    public function testConvertRelativeUrlToFilePathShouldConvertSpecialChars() {
        $relativeUrl = '/path/with/star*/doublequote"/and?query';
        $path = PathHelper::convertRelativeUrlToFilePath($relativeUrl);
        $this->assertEquals('/path/with/star%2A/doublequote%22/and/query', $path);
    }
}
