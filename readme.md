# MODX RTFM Convert

Project to convert MODX documentation from Confluence for import into MODX.

src/convert.php is the main entry point, which calls
\RtfmConvert\OldRtfmPageConverter::convertAll().

src/text-diff.php was used to generate the text diffs between oldrtfm and the
new rtfm pages.
