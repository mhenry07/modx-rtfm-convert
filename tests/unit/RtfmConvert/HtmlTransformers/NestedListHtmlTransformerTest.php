<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;

/**
 * Class NestedListHtmlTransformerTest
 * @package RtfmConvert\HtmlTransformers
 */
class NestedListHtmlTransformerTest extends HtmlTestCase {

    public function testTransformShouldPreserveSimpleList() {
        $html = '<ul><li>item</li></ul>';
        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($html, $result);
    }

    /**
     * @depends testTransformShouldPreserveSimpleList
     */
    public function testTransformShouldPreserveProperNestedList() {
        $html = '<ul><li>item 1<ul><li>item 1.1</li></ul></li></ul>';
        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($html, $result);
    }

    public function testTransformShouldMoveNestedUlIntoPrevLi() {
        $nested = '<ul><li>item 1.1</li></ul>';
        $html = <<<EOT
<ul>
    <li>item 1</li>
    {$nested}
</ul>
EOT;

        $expected = <<<EOT
<ul>
    <li>item 1
        {$nested}
    </li>
</ul>
EOT;

        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldMoveOlNestedInOlIntoPrevLi() {
        $nested = '<ol><li>item 1.1</li></ol>';
        $html = <<<EOT
<ol>
    <li>item 1</li>
    {$nested}
</ol>
EOT;

        $expected = <<<EOT
<ol>
    <li>item 1
        {$nested}
    </li>
</ol>
EOT;

        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    public function testTransformShouldWrapNestedUlWithNoPrevLiInNewLiAndWarn() {
        $nested = '<ul><li>item 1.1</li></ul>';
        $html = <<<EOT
<ul>
    {$nested}
</ul>
EOT;

        $expected = <<<EOT
<ul>
    <li>
        {$nested}
    </li>
</ul>
EOT;

        $pageData = new PageData($html, $this->stats);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('lists: nested', 1,
            array(self::TRANSFORM => 1, self::WARNING => 1));
    }

    public function testTransformShouldMoveNestedDivIntoPrevLi() {
        $nested = '<div>inner div</div>';
        $html = <<<EOT
<ul>
    <li>item 1</li>
    {$nested}
</ul>
EOT;

        $expected = <<<EOT
<ul>
    <li>item 1
        {$nested}
    </li>
</ul>
EOT;

        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    /**
     * @depends testTransformShouldMoveNestedUlIntoPrevLi
     */
    public function testTransformShouldMoveDeepNestedUlsIntoPrevLis() {
        $html = <<<EOT
<ul>
    <li>item 1</li>
    <ul>
        <li>item 1.1</li>
        <ul><li>item 1.1.1</li></ul>
    </ul>
</ul>
EOT;

        $expected = <<<EOT
<ul>
    <li>item 1
        <ul>
            <li>item 1.1
                <ul><li>item 1.1.1</li></ul>
            </li>
        </ul>
    </li>
</ul>
EOT;

        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    /**
     * @depends testTransformShouldMoveNestedUlIntoPrevLi
     */
    public function testTransformShouldPreserveContentBetweenNestedLists() {
        $html = <<<EOT
<ul>
    <li>item 1</li>
    <ul><li>item 1.1</li></ul>
    <li>item 2</li>
    <ul><li>item 2.1</li></ul>
    <li>item 3</li>
</ul>
<p>The land of in-between</p>
<ol>
    <li>item I</li>
    <ol><li>item I.A</li></ol>
</ol>
EOT;

        $expected = <<<EOT
<ul>
    <li>item 1
        <ul>
            <li>item 1.1</li>
        </ul>
    </li>
    <li>item 2
        <ul><li>item 2.1</li></ul>
    </li>
    <li>item 3</li>
</ul>
<p>The land of in-between</p>
<ol>
    <li>item I
        <ol><li>item I.A</li></ol>
    </li>
</ol>
EOT;

        $pageData = new PageData($html);
        $transformer = new NestedListHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

//    // looks like QueryPath converts ul > ol (nested) to ul + ol (sibling)
//    public function testTransformShouldMoveOlNestedInUlIntoPrevLi() {
//        $this->markTestSkipped('Cannot handle ul > ol due to the way QueryPath parses it.');
//        $nested = '<ol><li>item 1.1</li></ol>';
//        $html = <<<EOT
//<ul>
//    <li>item 1</li>
//    {$nested}
//</ul>
//EOT;
//
//        $expected = <<<EOT
//<ul>
//    <li>item 1
//        {$nested}
//    </li>
//</ul>
//EOT;
//
//        $pageData = new PageData($html);
//        $transformer = new NestedListHtmlTransformer();
//        $result = $transformer->transform($pageData);
//        $this->assertHtmlEquals($expected, $result);
//    }
//
//    // looks like QueryPath converts ol > ul (nested) to ol + ul (sibling)
//    public function testTransformShouldMoveUlNestedInOlIntoPrevLi() {
//        $this->markTestSkipped('Cannot handle ol > ul due to the way QueryPath parses it.');
//        $nested = '<ul><li>item 1.1</li></ul>';
//        $html = <<<EOT
//<ol>
//    <li>item 1</li>
//    {$nested}
//</ol>
//EOT;
//
//        $expected = <<<EOT
//<ol>
//    <li>item 1
//        {$nested}
//    </li>
//</ol>
//EOT;
//
//        $pageData = new PageData($html);
//        $transformer = new NestedListHtmlTransformer();
//        $result = $transformer->transform($pageData);
//        $this->assertHtmlEquals($expected, $result);
//    }
}
