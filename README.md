# [Beta] WordPress Replace Filename Accents Plugin

## Existing attachments

Accented characters in file names can be problematic. This plugin tries to replace accents in the file names of your **existing** attachments.
An admin page is added under the 'Tools' menu item where you can process your files.

**Important:** Be smart and [Back up your database and files](https://codex.wordpress.org/WordPress_Backups) before running the replacement process. If anything breaks, there is no "undo".

The WordPress `remove_accents` function might not work as expected due to different file name encodings. The plugin works best if you have the php [Normalizer](http://php.net/manual/en/class.normalizer.php) extension installed.

## ⚠️ Warning! Broken links and images ahead 😱!

The plugin currently does not replace references to files in post content like links and image tags. This will lead to broken links and images on your site. A `replace_filename_accents_renamed_file` hook is provided on which you can run your own functionality directly after a file has been renamed.

### Future proofing: replace accented characters when uploading new attachments

This plugin does not remove accents when uploading new files. It only works for existing attachments.
To replace accented characters on file upload, add a filter to `sanitize_filename`:

```
add_filter( 'sanitize_file_name', 'remove_accents' );
```

For a more thourough approach using the php Normalizer extension, if it is installed:

```
/**
 * Enhanced 'remove_accents' function. If the php Normalizer extension installed, use it.
 *
 * @param string $string Text that might have accent characters
 * @return string Filtered string with replaced "nice" characters.
 */
function prefix_remove_accents( $string ) {
	if ( function_exists( 'normalizer_normalize' ) ) {
		if ( ! normalizer_is_normalized( $string, Normalizer::FORM_C ) ) {
			$string = normalizer_normalize( $string, Normalizer::FORM_C );
		}
	}
	return remove_accents( $string );
}

add_filter( 'sanitize_file_name', 'prefix_remove_accents' );

```

## Todo

- Replace references to files in post content
- DRY up ajax() method