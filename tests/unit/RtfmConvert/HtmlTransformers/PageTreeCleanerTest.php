<?php
/**
 * @author: Mike Henry
 */

namespace RtfmConvert\HtmlTransformers;


use RtfmConvert\HtmlTestCase;
use RtfmConvert\PageData;

class PageTreeCleanerTest extends HtmlTestCase {

    public function testCleanNonPagetreeShouldPreserveContent() {
        $input = '<div><ul><li>item</li></ul></div>';
        $pageData = new PageData($input, $this->stats);
        $cleaner = new PageTreeCleaner();
        $result = $cleaner->clean($pageData);
        $this->assertHtmlEquals($input, $result);
    }

    public function testCleanShouldReturnExpectedList() {
        $pageData = new PageData($this->inputHtml, $this->stats);
        $cleaner = new PageTreeCleaner();
        $cleaner->setStatsPrefix('pagetree: ');
        $result = $cleaner->clean($pageData);
        $this->assertHtmlEquals($this->expectedHtml, $result);
        $this->assertTransformStat('pagetree: cleanup', 1);
    }

    public function testCleanShouldCleanTwoTrees() {
        $middleContent = '<div>other list <ul><li>list</li></ul></div>';
        $input = $this->inputHtml . $middleContent . $this->inputHtml;
        $expected = $this->expectedHtml . $middleContent . $this->expectedHtml;
        $pageData = new PageData($input, $this->stats);

        $cleaner = new PageTreeCleaner();
        $result = $cleaner->clean($pageData);
        $this->assertHtmlEquals($expected, $result);
    }

    // see http://oldrtfm.modx.com/display/ADDON/Advsearch.AdvSearchForm.tpl
    public function testCleanShouldRemovePagetreeWithError() {
        $html = <<<'EOT'
<h3>See Also</h3>
<div class="plugin_pagetree">
    <div id="pagetree-error" class="error">
        <span class="errorMessage">
            The root page AdvSearchForm could not be found in space MODx Add-Ons. <br>
        <span>
    </div>
</div>
EOT;
        $expected = '<h3>See Also</h3>';

        $pageData = new PageData($html, $this->stats);

        $cleaner = new PageTreeCleaner();
        $cleaner->setStatsPrefix('pagetree: ');
        $result = $cleaner->clean($pageData);

        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('pagetree: cleanup', 1);
    }

    // see http://oldrtfm.modx.com/display/ADDON/modActiveDirectory
    public function testCleanShouldRemoveEmptyTree() {
        $html = <<<'EOT'
<h3>See Also</h3>
<div class="plugin_pagetree">
    <ul class="plugin_pagetree_children_list plugin_pagetree_children_list_noleftspace">
        <div class="plugin_pagetree_children">
<ul class="plugin_pagetree_children_list" id="child_ul20119574-1">
    </ul>
        </div>

    <fieldset class="hidden">
        <input type="hidden" name="treeId" value="" /><input type="hidden" name="treeRequestId" value="/plugins/pagetree/naturalchildren.action?decorator=none&amp;excerpt=false&amp;sort=position&amp;reverse=false&amp;disableLinks=false" /><input type="hidden" name="treePageId" value="18678479" /><input type="hidden" name="noRoot" value="false" /><input type="hidden" name="rootPageId" value="18678541" /><input type="hidden" name="rootPage" value="" /><input type="hidden" name="startDepth" value="0" /><input type="hidden" name="spaceKey" value="revolution20" /><input type="hidden" name="i18n-pagetree.loading" value="Loading..." /><input type="hidden" name="i18n-pagetree.error.permission" value="Unable to load page tree. It seems that you do not have permission to view the root page." /><input type="hidden" name="i18n-pagetree.eeror.general" value="There was a problem retrieving the page tree. Please check the server log file for more information." /><input type="hidden" name="loginUrl" value="/login.action?os_destination=%2Fdisplay%2Frevolution20%2FAdvanced%2BInstallation" />
        <fieldset class="hidden">
            <input type="hidden" name="ancestorId" value="18678541" />
        </fieldset>
    </fieldset>
</div>
EOT;
        $expected = '<h3>See Also</h3>';

        $pageData = new PageData($html, $this->stats);

        $cleaner = new PageTreeCleaner();
        $cleaner->setStatsPrefix('pagetree: ');
        $result = $cleaner->clean($pageData);

        $this->assertHtmlEquals($expected, $result);
        $this->assertTransformStat('pagetree: cleanup', 1,
            array(self::TRANSFORM => 1));
    }

    protected $inputHtml = <<<'EOT'
<div class="plugin_pagetree">
    <ul class="plugin_pagetree_children_list plugin_pagetree_children_list_noleftspace">
        <div class="plugin_pagetree_children">
            <ul class="plugin_pagetree_children_list" id="child_ul18678541-1">
                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                        <a id="plusminus18678053-1" class="plugin_pagetree_childtoggle icon" href=""></a>
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan18678053-1"><a href="/display/revolution20/Basic+Installation">Basic Installation</a></span>
                    </div>

                    <div id="children18678053-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul18678053-1">
                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan18678117-1"><a href="/display/revolution20/MODx+Revolution+on+Debian">MODx Revolution on Debian</a></span>
                                </div>

                                <div id="children18678117-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul18678117-1">
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan18678121-1"><a href="/display/revolution20/Lighttpd+Guide">Lighttpd Guide</a></span>
                                </div>

                                <div id="children18678121-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul18678121-1">
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan18678115-1"><a href="/display/revolution20/Problems+with+WAMPServer+2.0i">Problems with WAMPServer 2.0i</a></span>
                                </div>

                                <div id="children18678115-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul18678115-1">
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan36634874-1"><a href="/display/revolution20/Installation+on+a+server+running+ModSecurity">Installation on a server running ModSecurity</a></span>
                                </div>

                                <div id="children36634874-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul36634874-1">
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan36634637-1"><a href="/display/revolution20/MODX+and+Suhosin">MODX and Suhosin</a></span>
                                </div>

                                <div id="children36634637-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul36634637-1">
                                    </ul>
                                </div>
                            </li>

                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan34636294-1"><a href="/display/revolution20/Nginx+Server+Config">Nginx Server Config</a></span>
                                </div>

                                <div id="children34636294-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul34636294-1">
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan18678479-1"><a href="/display/revolution20/Advanced+Installation">Advanced Installation</a></span>
                    </div>

                    <div id="children18678479-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul18678479-1">
                        </ul>
                    </div>
                </li>

                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan18678547-1"><a href="/display/revolution20/Git+Installation">Git Installation</a></span>
                    </div>

                    <div id="children18678547-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul18678547-1">
                        </ul>
                    </div>
                </li>

                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                        <a id="plusminus36110340-1" class="plugin_pagetree_childtoggle icon" href=""></a>
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan36110340-1"><a href="/display/revolution20/Command+Line+Installation">Command Line Installation</a></span>
                    </div>

                    <div id="children36110340-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul36110340-1">
                            <li>
                                <div class="plugin_pagetree_childtoggle_container">
                                </div>

                                <div class="plugin_pagetree_children_content">
                                    <span class="plugin_pagetree_children_span" id="childrenspan36110345-1"><a href="/display/revolution20/The+Setup+Config+Xml+File">The Setup Config Xml File</a></span>
                                </div>

                                <div id="children36110345-1" class="plugin_pagetree_children_container">
                                    <ul class="plugin_pagetree_children_list" id="child_ul36110345-1">
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </li>

                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan18678114-1"><a href="/display/revolution20/Troubleshooting+Installation">Troubleshooting Installation</a></span>
                    </div>

                    <div id="children18678114-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul18678114-1">
                        </ul>
                    </div>
                </li>

                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan18678051-1"><a href="/pages/viewpage.action?pageId=18678051">Successful Installation, Now What Do I Do?</a></span>
                    </div>

                    <div id="children18678051-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul18678051-1">
                        </ul>
                    </div>
                </li>

                <li>
                    <div class="plugin_pagetree_childtoggle_container">
                    </div>

                    <div class="plugin_pagetree_children_content">
                        <span class="plugin_pagetree_children_span" id="childrenspan18678111-1"><a href="/display/revolution20/Using+MODx+Revolution+from+SVN">Using MODx Revolution from SVN</a></span>
                    </div>

                    <div id="children18678111-1" class="plugin_pagetree_children_container">
                        <ul class="plugin_pagetree_children_list" id="child_ul18678111-1">
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
    </ul>

    <fieldset class="hidden">
        <input type="hidden" name="treeId" value="" /><input type="hidden" name="treeRequestId" value="/plugins/pagetree/naturalchildren.action?decorator=none&amp;excerpt=false&amp;sort=position&amp;reverse=false&amp;disableLinks=false" /><input type="hidden" name="treePageId" value="18678479" /><input type="hidden" name="noRoot" value="false" /><input type="hidden" name="rootPageId" value="18678541" /><input type="hidden" name="rootPage" value="" /><input type="hidden" name="startDepth" value="0" /><input type="hidden" name="spaceKey" value="revolution20" /><input type="hidden" name="i18n-pagetree.loading" value="Loading..." /><input type="hidden" name="i18n-pagetree.error.permission" value="Unable to load page tree. It seems that you do not have permission to view the root page." /><input type="hidden" name="i18n-pagetree.eeror.general" value="There was a problem retrieving the page tree. Please check the server log file for more information." /><input type="hidden" name="loginUrl" value="/login.action?os_destination=%2Fdisplay%2Frevolution20%2FAdvanced%2BInstallation" />
        <fieldset class="hidden">
            <input type="hidden" name="ancestorId" value="18678541" />
        </fieldset>
    </fieldset>
</div>
EOT;

    protected $expectedHtml = <<<'EOT'
<div class="plugin_pagetree">
    <ul>
        <li>
            <a href="/display/revolution20/Basic+Installation">Basic Installation</a>
            <ul>
                <li>
                    <a href="/display/revolution20/MODx+Revolution+on+Debian">MODx Revolution on Debian</a>
                </li>

                <li>
                    <a href="/display/revolution20/Lighttpd+Guide">Lighttpd Guide</a>
                </li>

                <li>
                    <a href="/display/revolution20/Problems+with+WAMPServer+2.0i">Problems with WAMPServer 2.0i</a>
                </li>

                <li>
                    <a href="/display/revolution20/Installation+on+a+server+running+ModSecurity">Installation on a server running ModSecurity</a>
                </li>

                <li>
                        <a href="/display/revolution20/MODX+and+Suhosin">MODX and Suhosin</a>
                </li>

                <li>
                    <a href="/display/revolution20/Nginx+Server+Config">Nginx Server Config</a>
                </li>
            </ul>
        </li>

        <li>
            <a href="/display/revolution20/Advanced+Installation">Advanced Installation</a>
        </li>

        <li>
            <a href="/display/revolution20/Git+Installation">Git Installation</a>
        </li>

        <li>
            <a href="/display/revolution20/Command+Line+Installation">Command Line Installation</a>
            <ul>
                <li>
                    <a href="/display/revolution20/The+Setup+Config+Xml+File">The Setup Config Xml File</a>
                </li>
            </ul>
        </li>

        <li>
                <a href="/display/revolution20/Troubleshooting+Installation">Troubleshooting Installation</a>
        </li>

        <li>
            <a href="/pages/viewpage.action?pageId=18678051">Successful Installation, Now What Do I Do?</a>
        </li>

        <li>
            <a href="/display/revolution20/Using+MODx+Revolution+from+SVN">Using MODx Revolution from SVN</a>
        </li>
    </ul>
</div>
EOT;

}
