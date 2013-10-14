# MODX RTFM Convert

Project to convert MODX documentation from Confluence for import into MODX.

src/convert.php is the main entry point, which calls
\RtfmConvert\OldRtfmPageConverter::convertAll().

src/import.php imports converted data into MODX. See rtfmimport-readme.md.

src/compare.php compares two RTFM sites page-by-page, including a text diff,
pre element comparison and page outline comparison. Example usage:

    php src/compare.php oldrtfm.modx.com rtfm.modx.com
