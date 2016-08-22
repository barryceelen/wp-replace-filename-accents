<?php
/**
 * Plugin admin page template
 *
 * @package    WordPress
 * @subpackage ReplaceFilenameAccents
 * @author     Barry Ceelen
 * @license    GPL-3.0+
 * @link       https://github.com/barryceelen/wp-replace-filename-accents
 * @copyright  Barry Ceelen
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
?>
<div class="wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>
	<noscript>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'You must enable Javascript in order to proceed.', 'replace-filename-accents' ) ?></p>
		</div>
	</noscript>

	<?php if ( 0 === (int) $count ) : ?>

		<p><?php esc_html_e( 'No media files found.' ); ?></p>

	<?php else : ?>

		<div class="notice notice-warning hide-if-no-js">
			<p>
			<?php _e( '<strong>Important:</strong> before continuing, please <a href="https://codex.wordpress.org/WordPress_Backups">back up your database and files</a>. Accent removal is not reversible.'); ?>
			</p>
		</div>
		<form method="post" action="">
			<?php wp_nonce_field( 'remove_filename_accents', 'remove_filename_accents' ); ?>
			<p><?php esc_html_e( 'Use this tool to retroactively remove accented characters from your attachment filenames.', 'replace-filename-accents' ); ?><br> <?php _e( '<strong>Note:</strong> URLs in the content of posts will currently not be changed. This may result in broken images or links due to hard-coded URLs.', 'replace-filename-accents' ); ?></p>
			<br />
			<p><input type="submit" class="button button-primary hide-if-no-js" name="replace-filename-accents" id="replace-filename-accents" value="<?php esc_html_e( 'Replace Filename Accents', 'replace-filename-accents' ) ?>" /></p>
		</form>

	<?php endif; ?>
</div>