<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class ConfluenceTableHtmlTransformerTest extends HtmlTestCase {

    public function testTransformShouldStripTableWrapper() {
        $input = <<<'EOT'
<div class='table-wrap'>
<table class='confluenceTable'><tr><td>cell</td></tr></table>
</div>
EOT;

        $pageData = new PageData($input);
        $transformer = new ConfluenceTableHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertNotEquals('div', $result->find('table')->parent()->tag());
    }

    /**
     * @depends testTransformShouldStripTableWrapper
     */
    public function testTransformShouldRemoveConfluenceTableClasses() {
        $input = <<<'EOT'
<div class='table-wrap'>
<table class='confluenceTable'><tbody>
<tr>
<th class='confluenceTh'> <b><em>Content Elements</em></b> </th>
<th class='confluenceTh'> Revolution (New) </th>
<th class='confluenceTh'> Example for Revolution <br class="atl-forced-newline" /> </th>
</tr>
<tr>
<td class='confluenceTd'> Resource Fields </td>
<td class='confluenceTd'> [[*field]] <br class="atl-forced-newline" /> </td>
<td class='confluenceTd'> [[*pagetitle]] </td>
</tr>
</tbody></table>
</div>
EOT;

        $expected = <<<'EOT'
<table><tbody>
<tr>
<th> <b><em>Content Elements</em></b> </th>
<th> Revolution (New) </th>
<th> Example for Revolution <br class="atl-forced-newline" /> </th>
</tr>
<tr>
<td> Resource Fields </td>
<td> [[*field]] <br class="atl-forced-newline" /> </td>
<td> [[*pagetitle]] </td>
</tr>
</tbody></table>
EOT;

        $pageData = new PageData($input);
        $transformer = new ConfluenceTableHtmlTransformer();
        $result = $transformer->transform($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    /**
     * @depends testTransformShouldStripTableWrapper
     */
    public function testTransformShouldGenerateExpectedStat() {
        $input = <<<'EOT'
<div class='table-wrap'>
<table class='confluenceTable'><tr><td>cell</td></tr></table>
</div>
EOT;

        $pageData = new PageData($input, $this->stats);
        $transformer = new ConfluenceTableHtmlTransformer();
        $transformer->transform($pageData);
        $this->assertStat('div.table-wrap table.confluenceTable', 1, true);
    }
}
