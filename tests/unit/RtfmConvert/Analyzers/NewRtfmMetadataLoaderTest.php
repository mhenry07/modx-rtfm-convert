<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\Analyzers;


use RtfmConvert\PageData;
use RtfmConvert\PageStatistics;

class NewRtfmMetadataLoaderTest extends \PHPUnit_Framework_TestCase {

    public function testProcessShouldLoadExpectedMetadata() {
        $html = <<<'EOT'
<!doctype html>
<html class="no-js" lang="en">
<head>
<title>Getting Started | MODX Revolution</title>
<meta name="author" content="JP DeVries">
<link rel="canonical" href="http://rtfm.modx.com/revolution/2.x/getting-started">
<body class="search-modal-hidden doc-report-hidden twocol p-3 t-1 search-newb" data-page-id="152" data-uri="/revolution/2.x/getting-started">
<div class="inside-content container ug" role="main">
<section class="body-section row-fluid center">
<div class="span8 content">
<section>
<header>
<h1>Getting Started</h1>
	<h5>Last edited by JP DeVries on Aug  8, 2013. </h5>
</header>
<!-- start content -->
<p>
	            Welcome to MODX Revolution. This section provides installation tutorials, beginning concepts around MODX, and general information about MODX to get you started.
</p>
<ol class="ug-toc">
	<li><a href="revolution/2.x/getting-started/server-requirements">Server Requirements</a></li>
	<li><a href="revolution/2.x/getting-started/installation">Installation</a></li>
	<li><a href="revolution/2.x/getting-started/an-overview-of-modx">An Overview of MODX</a></li>
</ol>
<!-- end content -->
</section>
</div>
</section>
</div>
</body>
</html>
EOT;

        $pageLoader = $this
            ->getMock('\RtfmConvert\Infrastructure\CachedPageLoader');
        $pageLoader->expects($this->any())->method('get')
            ->with('http://rtfm.modx.com/display/revolution20/Getting+Started')
            ->will($this->returnValue($html));

        $stats = new PageStatistics();
        $stats->addValueStat(PageStatistics::PATH_LABEL,
            '/display/revolution20/Getting+Started');
        $pageData = new PageData('', $stats);
        $loader = new NewRtfmMetadataLoader($pageLoader);
        $loader->setBaseUrl('http://rtfm.modx.com');
        $loader->setStatsPrefix('dest: ');
        $loader->setCacheDirectory('../data/cache');
        $result = $loader->process($pageData);

        $this->assertEquals(
            'http://rtfm.modx.com/revolution/2.x/getting-started',
            $stats->getStat(NewRtfmMetadataLoader::DEST_URL_LABEL,
                PageStatistics::VALUE));
        $this->assertEquals('152',
            $stats->getStat(NewRtfmMetadataLoader::DEST_PAGE_ID_LABEL,
                PageStatistics::VALUE));
        $this->assertEquals('JP DeVries',
            $stats->getStat(NewRtfmMetadataLoader::DEST_AUTHOR_LABEL,
                PageStatistics::VALUE));
        $this->assertEquals('Getting Started',
            $stats->getStat(NewRtfmMetadataLoader::DEST_TITLE_LABEL,
                PageStatistics::VALUE));
        $this->assertEquals('Last edited by JP DeVries on Aug  8, 2013.',
            $stats->getStat(NewRtfmMetadataLoader::DEST_MODIFICATION_INFO_LABEL,
                PageStatistics::VALUE));
    }
}
