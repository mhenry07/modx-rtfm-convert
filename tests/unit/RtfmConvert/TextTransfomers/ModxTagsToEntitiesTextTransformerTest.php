<?php
/**
 * User: mhenry
 * Date: 8/25/13
 */

namespace RtfmConvert;


use RtfmConvert\TextTransformers\ModxTagsToEntitiesTextTransformer;

class ModxTagsToTextTransformerTest extends \PHPUnit_Framework_TestCase {
    /** @var ModxTagsToEntitiesTextTransformer */
    protected $transformer;

    protected function setUp() {
        $this->transformer = new ModxTagsToEntitiesTextTransformer();
    }

    public function testTransformEmptyShouldReturnEmpty() {
        $result = $this->transformer->transform('');
        $this->assertEquals('', $result);
    }

    public function testTransformTextShouldReturnText() {
        $result = $this->transformer->transform('text');
        $this->assertEquals('text', $result);
    }

    public function testTransformLeftBracketShouldReturnEntity91() {
        $result = $this->transformer->transform('[');
        $this->assertEquals('&#91;', $result);
    }

    public function testTransformRightBracketShouldReturnEntity93() {
        $result = $this->transformer->transform(']');
        $this->assertEquals('&#93;', $result);
    }

    public function testTransformIdResourceFieldShouldReturnExpected() {
        $result = $this->transformer->transform('[[*id]]');
        $this->assertEquals('&#91;&#91;*id&#93;&#93;', $result);
    }

    public function testTransformComplexStringShouldReturnExpectedString() {
        $input = <<<'EOT'
<h2>Revolution Tags</h2>
<p>
    [[*pagetitle]] [[*tags]] [[$header]]
    [[getResources]] [[+modx.user.id]]
    [[~[[*id]]? &amp;scheme=`full`]] [[++site_start]]
    [[%LanguageStringKey? &amp;language=`en` &amp;namespace=`NameSpaceName` &amp;topic=`TopicName`]]
    [[-this is a comment]]
    <code>&amp;param=`?=&amp;is ok now, wow!?&amp;=`</code>
</p>
<p><a href="[[*id]]">link</a></p>
<pre>
[[MySnippet@myPropSet:filter1:filter2=`modifier`? &amp;prop1=`x` &amp;prop2=`y`]]
[[!getResources? &amp;parents=`123` &amp;limit=`5`]]
</pre>
<h2>Evolution Tags</h2>
<p>
    [*field*] [*templatevar*] {{chunk }} [[snippet]]
    [+placeholder+] [~link~] [(system_setting)]
</p>
<p>Total time: [^t^]</p>
EOT;

        $expected = <<<'EOT'
<h2>Revolution Tags</h2>
<p>
    &#91;&#91;*pagetitle&#93;&#93; &#91;&#91;*tags&#93;&#93; &#91;&#91;$header&#93;&#93;
    &#91;&#91;getResources&#93;&#93; &#91;&#91;+modx.user.id&#93;&#93;
    &#91;&#91;~&#91;&#91;*id&#93;&#93;? &amp;scheme=`full`&#93;&#93; &#91;&#91;++site_start&#93;&#93;
    &#91;&#91;%LanguageStringKey? &amp;language=`en` &amp;namespace=`NameSpaceName` &amp;topic=`TopicName`&#93;&#93;
    &#91;&#91;-this is a comment&#93;&#93;
    <code>&amp;param=`?=&amp;is ok now, wow!?&amp;=`</code>
</p>
<p><a href="&#91;&#91;*id&#93;&#93;">link</a></p>
<pre>
&#91;&#91;MySnippet@myPropSet:filter1:filter2=`modifier`? &amp;prop1=`x` &amp;prop2=`y`&#93;&#93;
&#91;&#91;!getResources? &amp;parents=`123` &amp;limit=`5`&#93;&#93;
</pre>
<h2>Evolution Tags</h2>
<p>
    &#91;*field*&#93; &#91;*templatevar*&#93; {{chunk }} &#91;&#91;snippet&#93;&#93;
    &#91;+placeholder+&#93; &#91;~link~&#93; &#91;(system_setting)&#93;
</p>
<p>Total time: &#91;^t^&#93;</p>
EOT;

        $result = $this->transformer->transform($input);
        $this->assertEquals($expected, $result);
    }
}
