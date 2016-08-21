=== Replace Filename Accents ===
Contributors: barryceelen
Tags: attachments, accents
Requires at least: 3.5.0
Tested up to: 4.6
Stable tag: trunk
License: GPL3+

Retroactively replaces accents in existing attachment file names.

== Description ==

Accented characters in file names can be problematic. This plugin tries to replace accents in the file names of your **existing** attachments.
An admin page is added under the 'Tools' menu item where you can process your files.

**Important:** Be smart and [Back up your database and files](https://codex.wordpress.org/WordPress_Backups) before running the replacement process. If anything breaks, there is no "undo".

The WordPress `remove_accents` function might not work as expected due to different file name encodings. The plugin works best if you have the php [Normalizer](http://php.net/manual/en/class.normalizer.php) extension installed.

= Warning! Broken links and images ahead =

The plugin currently does not replace references to files in post content like links and image tags. This will lead to broken links and images on your site. A `replace_filename_accents_renamed_file` hook is provided on which you can run your own functionality directly after a file has been renamed.


== ChangeLog ==

= Version 0.0.1 =

* Initial release.
