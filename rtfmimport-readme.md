RtfmImport
==========

Usage
-----

Once the package has been installed, open the following URL to run the import 
with default options:
{rtfm_url}/core/components/model/rtfm-convert/src/import.php
e.g. http://rtfm.modx.com/core/components/model/rtfm-convert/src/import.php

Options:
* fix_links_for_base_href: specify whether to fix links for base href on import
    * 0 (default): don't fix links (assumes LinkFixerForBaseHref plugin will be used)
    * 1: fix links
    * e.g. http://rtfm.modx.com/core/components/model/rtfm-convert/src/import.php?fix_links_for_base_href=0
* update_confluence_hrefs: specify whether to update old confluence relative
  urls to new rtfm urls
    * 0: don't update links
    * 1 (default): update links
    * e.g. http://rtfm.modx.com/core/components/model/rtfm-convert/src/import.php?update_confluence_hrefs=1
* use_modx_link_tags: Specify whether to use modx link tags or not when updating
  conluence hrefs. Only applies if update_confluence_hrefs=1.
    * 0 (default): don't use modx link tags (use friendly urls)
    * 1: use modx link tags
    * e.g. http://rtfm.modx.com/core/components/model/rtfm-convert/src/import.php?update_confluence_hrefs=1&use_modx_link_tags=1


Building the transport package
------------------------------

Before building the transport package, make sure composer dependencies have been
installed via `composer install` from core/components/rtfmimport/model/rtfm-convert

Also, run the rtfm conversion via `php src/convert.php` from the same directory.

Then, run `php _build/build.transport.php` from the project root directory to
build the transport package.
