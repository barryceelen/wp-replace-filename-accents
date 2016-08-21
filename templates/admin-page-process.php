<?php
/**
 * Plugin admin process page template
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

global $attachments;
?>
<div class="wrap" id="replace-filename-accents-wrap">
	<h2><?php esc_html_e( 'Replace Filename Accents', 'replace-filename-accents' ); ?></h2>
	<noscript>
		<div id="message" class="notice notice-error">
			<p><?php esc_html_e( 'You must enable Javascript in order to proceed.', 'replace-filename-accents' ) ?></p>
		</div>
	</noscript>
	<?php if ( ! $count ) : ?>
		<p><?php printf( __( "Unable to find any attachments. Are you sure <a href='%s'>some exist</a>?", 'replace-filename-accents' ), admin_url( 'upload.php' ) ); ?></p>
	<?php else: ?>
		<div id="replace-filename-accents-bar">
			<div id="replace-filename-accents-bar-percent">0%</div>
		</div>
		<p>
			<?php
			printf(
				'<input type="button" class="button button-cancel hide-if-no-js replace-filename-accents-stop" value="%1$s" /> <a class="replace-filename-accents-restart hidden" aria-hidden="true" href="%2$s">%3$s</a>',
				esc_html__( 'Abort Replacing Accents', 'replace-filename-accents' ),
				$this->_tools_page_url,
				esc_html__( 'Restart', 'replace-filename-accents' )
			);
			?>
		</p>

		<p>
			<?php printf( __( 'Total Files: %s', 'replace-filename-accents' ), $count ); ?><br />
			<?php printf( __( 'Files Renamed: %s', 'replace-filename-accents' ), '<span id="replace-filename-accents-renamedcount">0</span>' ); ?><br />
			<?php printf( __( 'Rename Failures: %s', 'replace-filename-accents' ), '<span id="replace-filename-accents-errorcount">0</span>' ); ?>
		</p>

		<ul id="replace-filename-accents-successlist">
		</ul>
	<?php endif; ?>
</div>