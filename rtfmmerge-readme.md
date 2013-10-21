RtfmMerge
==========

Usage
-----

Once the package has been installed, open the following URL to run the merge import 
with default options:
{rtfm_url}/core/components/rtfmmerge/model/rtfm-convert/src/merge.import.php
e.g. http://rtfm.modx.com/core/components/rtfmmerge/model/rtfm-convert/src/merge.import.php

Options:
* normalize_links: specify whether to normalize root slashes and anchors in links. This is similar to fix_links_for_base_href but more comprehensive.
    * 0: don't normalize links
    * 1 (default): normalize links
    * e.g. http://rtfm.modx.com/core/components/rtfmmerge/model/rtfm-convert/src/merge.import.php?normalize_links=1
* fix_links_for_base_href: specify whether to fix links for base href on import
    * 0 (default): don't fix links (assumes LinkFixerForBaseHref plugin will be used)
    * 1: fix links
    * e.g. http://rtfm.modx.com/core/components/rtfmmerge/model/rtfm-convert/src/merge.import.php?fix_links_for_base_href=0
* update_confluence_hrefs: specify whether to update old confluence relative
  urls to new rtfm urls
    * 0: don't update links
    * 1 (default): update links
    * e.g. http://rtfm.modx.com/core/components/rtfmmerge/model/rtfm-convert/src/merge.import.php?update_confluence_hrefs=1
* use_modx_link_tags: Specify whether to use modx link tags or not when updating
  conluence hrefs. Only applies if update_confluence_hrefs=1.
    * 0 (default): don't use modx link tags (use friendly urls)
    * 1: use modx link tags
    * e.g. http://rtfm.modx.com/core/components/rtfmmerge/model/rtfm-convert/src/merge.import.php?update_confluence_hrefs=1&use_modx_link_tags=1


Building the transport package
------------------------------

Before building the transport package, make sure composer dependencies have been
installed via `composer install` from core/components/rtfmmerge/model/rtfm-convert

Also, merge contributed changes with converted pages using git, various merge 
scripts, etc. There were multiple steps involved and several merge conflicts 
that had to be resolved manually. The resulting data is in data/merge.

Then, run `php _build/build.transport.php` from the project root directory to
build the transport package.
