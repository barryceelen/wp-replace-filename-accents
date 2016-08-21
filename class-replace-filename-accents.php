<?php
/**
 * Contains plugin class
 *
 * @package    WordPress
 * @subpackage ReplaceFilenameAccents
 * @author     Barry Ceelen
 * @license    GPL-3.0+
 * @link       https://github.com/barryceelen/wp-replace-filename-accents
 * @copyright  Barry Ceelen
 */

/**
 * Plugin class.
 *
 * @since 0.0.1
 */
class ReplaceFilenameAccents {

	/**
	 * Instance of this class.
	 *
	 * @since 0.0.1
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Settings page hook.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	private $_hook_suffix;

	/**
	 * Capability required to use this plugin.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	private $_capability;

	/**
	 * URL of the admin page for this plugin.
	 *
	 * @since 0.0.1
	 *
	 * @var string
	 */
	private $_tools_page_url;

	/**
	 * Return an instance of this class.
	 *
	 * @since 0.0.1
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Initialize this class.
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		// Allow changing the capability required to use this plugin.
		$this->_capability = apply_filters( 'replace_filename_accents_cap', 'manage_options' );

		// Add admin menu item.
		add_action( 'admin_menu', array( $this, 'add_management_page' ) );

		// Enqueue styles and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add an action link pointing to the options page.
		$plugin_basename = plugin_basename( plugin_dir_path( __FILE__ ) . 'replace-filename-accents.php' );
		add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_plugin_action_link' ) );

		// Ajax action.
		add_action( 'wp_ajax_replace-filename-accents', array( $this, 'ajax' ) );
	}

	/**
	 * Add admin menu item.
	 *
	 * @since 0.0.1
	 */
	public function add_management_page() {

		$this->_hook_suffix = add_management_page(
			__( 'Replace Filename Accents', 'replace-filename-accents' ),
			__( 'Filename Accents', 'replace-filename-accents' ),
			$this->_capability,
			'replace-filename-accents',
			array( $this, 'management_page' )
		);

		$this->_tools_page_url = admin_url( 'tools.php?page=' . str_replace( 'tools_page_', '', $this->_hook_suffix ) );
	}

	/**
	 * Render plugin management page.
	 *
	 * @since 0.0.1
	 */
	public function management_page() {

		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'attachment'" );

		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && ! empty( $_POST['remove_filename_accents'] ) && wp_verify_nonce( $_POST['remove_filename_accents'], 'remove_filename_accents' ) ) {
			require( 'templates/admin-page-process.php' );
		} else {
			require( 'templates/admin-page-form.php' );
		}
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 0.0.1
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	public function enqueue_scripts( $hook_suffix ) {

		if ( $hook_suffix !== $this->_hook_suffix ) {
			return;
		}

		global $wpdb;

		$ids         = array();
		$attachments = $wpdb->get_results( "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' ORDER BY ID DESC" );

		if ( ! $attachments ) {
			return;
		}

		wp_enqueue_style(
			'replace-filename-accents',
			plugins_url( 'css/replace-filename-accents.css', __FILE__ ),
			array(),
			null
		);

		if ( empty( $_POST['replace-filename-accents'] ) || ! wp_verify_nonce( $_POST['remove_filename_accents'], 'remove_filename_accents' ) ) {
			return;
		}

		foreach ( $attachments as $attachment ) {
			$ids[] = $attachment->ID;
		}

		unset( $attachments );

		wp_enqueue_script(
			'replace-filename-accents',
			plugins_url( 'js/replace-filename-accents.js', __FILE__ ),
			array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-progressbar' ),
			null,
			true
		);

		wp_localize_script(
			'replace-filename-accents',
			'ReplaceFilenameAccentsVars',
			array(
				'ids' => wp_json_encode( $ids ),
				'labelComplete' => esc_html__( 'Complete!', 'replace-filename-accents' ),
				'labelAborted' => esc_html__( 'Aborted', 'replace-filename-accents' ),
				'labelErrorUnknown' => esc_js( __( 'An unknown error occurred.', 'replace-filename-accents' ) ),
				'labelStopping' => esc_js( __( 'Stopping...', 'replace-filename-accents' ) ),
				'returnLink' => esc_js( '<a href="' . $this->_tools_page_url . '">' . __( 'Restart', 'replace-filename-accents' ) . '</a>' ),
			)
		);
	}

	/**
	 * Add a link to the rename files admin page to the plugins page.
	 *
	 * @since 0.0.1
	 *
	 * @param array $links An array of action links.
	 * @return array Modified array of action links.
	 */
	public function add_plugin_action_link( $links ) {

		if ( current_user_can( $this->_capability ) ) {
			$link = sprintf(
				'<a href="%s">%s</a>',
				$this->_tools_page_url,
				esc_html__( 'Replace Accents', 'replace-filename-accents' )
			);
			$links = array_merge( array( 'settings' => $link ), $links );
		}

		return $links;
	}

	/**
	 * Ajax handler.
	 *
	 * @todo Replace references to images in posts.
	 * @todo The function assumes resized versions of an image are only accented if the original size
	 *       file name is accented and vice versa. This may not be true.
	 * @todo It is all very verbose y'all. DRY me.
	 *
	 * @since 0.0.1
	 */
	public function ajax() {

		if ( ! current_user_can( $this->_capability ) ) {
			wp_send_json_error( array( 'messages' => array( __( 'Your user account does not have permission to rename images', 'replace-filename-accents' ) ) ) );
		}

		$post = get_post( (int) $_REQUEST['id'] );

		if ( ! $post ) {
			wp_send_json_error( array( 'messages' => array( sprintf( __( 'Post does not exist. [%d]', 'replace-filename-accents' ), $_REQUEST['id'] ) ), ) );
		}

		if ( 'attachment' !== $post->post_type ) {
			wp_send_json_error( array( 'messages' => array( sprintf( __( 'Post is not an attachment. [%d]', 'replace-filename-accents' ), $post->ID ) ) ) );
		}

		$file_path       = get_attached_file( $post->ID, true );
		$file_path_parts = pathinfo( $file_path );
		$filename        = $this->normalize( $file_path_parts['basename'] );
		$filename_new    = remove_accents( $filename );

		if ( $filename_new === $filename ) {
			wp_send_json_success( array( 'renamed' => false ) );
		}

		/*
		 * If the file does not exist, return an error.
		 * Note: The file might exist but cannot be found due to filename encoding problems,
		 *       see: https://core.trac.wordpress.org/ticket/35951.
		 */
		if ( false === $file_path || ! file_exists( $file_path ) ) {
			wp_send_json_error( array( 'messages' => array( sprintf( __( 'The originally uploaded file cannot be found at %s', 'replace-filename-accents' ), '<code>' . $file_path . '</code>' ) ) ) );
		}

		$filename_unique = wp_unique_filename( $file_path_parts['dirname'], $filename_new );
		$rename = rename( $file_path, $file_path_parts['dirname'] . '/' . $filename_unique );

		if ( ! $rename ) {
			wp_send_json_error( array( 'messages' => array( sprintf( __( 'Could not rename %1$s to %2$s', 'replace-filename-accents' ), '<code>' . (string) $filename . '</code>', '<code>' . (string) $filename_unique . '</code>' ) ) ) );
		} else {

			update_attached_file( $post->ID, $file_path_parts['dirname'] . '/' . $filename_unique );

			/**
			 * Fires after a file is renamed.
			 *
			 * @since 0.0.1
			 *
			 * @param WP_Post $post            Attachment post object.
			 * @param string  $filename        Original file name.
			 * @param string  $filename_unique New file name.
			 */
			do_action( 'replace_filename_accents_renamed_file', $post, $filename, $filename_unique );

			$messages   = array();
			$messages[] = sprintf( __( 'Renamed %1$s to %2$s', 'replace-filename-accents' ), '<code>' . (string) $filename . '</code>', '<code>' . (string) $filename_unique . '</code>' );

			if ( preg_match( '!^image/!', get_post_mime_type( $post ) ) ) {

				$metadata = wp_get_attachment_metadata( $post->ID, true );

				if ( ! empty( $metadata ) ) {

					$metadata['file'] = _wp_relative_upload_path( $file_path_parts['dirname'] . '/' . $filename_unique );

					$sizes = array();

					// Different image sizes might point to the same file.
					if ( ! empty( $metadata['sizes'] ) ) {
						foreach( $metadata['sizes'] as $name => $arr ) {
							$sizes[ $arr['file'] ][] = $name;
						}
					}

					foreach ( $sizes as $file => $sizes ) {

						$filename     = $this->normalize( $file );
						$filename_new = remove_accents( $filename );

						if ( $filename === $filename_new ) { // Not entirely impossible.
							continue;
						}

						if ( ! file_exists( $file_path_parts['dirname'] . '/' . $filename ) ) {
							$messages[] = sprintf( __( 'The originally uploaded file cannot be found at %s', 'replace-filename-accents' ), '<code>' . $file_path_parts['dirname'] . '/' . $filename_new . '</code>' );
							continue;
						}

						$filename_unique = wp_unique_filename( $file_path_parts['dirname'], $filename_new );
						$rename = rename( $file_path_parts['dirname'] . '/' . $filename, $file_path_parts['dirname'] . '/' . $filename_unique );

						if ( ! $rename ) {
							$messages[] = sprintf( __( 'Could not rename %1$s to %2$s', 'replace-filename-accents' ), '<code>' . (string) $filename . '</code>', '<code>' . (string) $filename_unique . '</code>' );
						} else {
							$messages[] = sprintf( __( 'Renamed %1$s to %2$s', 'replace-filename-accents' ), '<code>' . (string) $filename . '</code>', '<code>' . (string) $filename_unique . '</code>' );

							foreach( $sizes as $size ) {
								$metadata['sizes'][ $size ]['file'] = $filename_unique;
							}

							/**
							 * Fires after a file is renamed.
							 *
							 * @since 0.0.1
							 *
							 * @param WP_Post $post            Attachment post object.
							 * @param string  $filename        Original file name.
							 * @param string  $filename_unique New file name.
							 */
							do_action( 'replace_filename_accents_renamed_file', $post, $filename, $filename_unique );
						}
					}

					wp_update_attachment_metadata( $post->ID, $metadata );
				}
			}
		}

		wp_send_json_success(
			array(
				'messages' => $messages,
				'renamed'  => true,
			)
		);
	}

	/**
	 * Things might not work as expected if you do not have the php Normalizer extension installed.
	 * See: https://core.trac.wordpress.org/ticket/35951
	 *
	 * I got it up and running on a Mac using Homebrew:
	 *
	 * $ brew install php56-intl (Or whatever php version you are using)
	 *
	 * Open php.ini and add: extension="/usr/local/opt/php55-intl/intl.so" (Your location of intl.so might vary)
	 *
	 * @param string $filename File name.
	 */
	private function normalize( $filename ) {

		if ( function_exists( 'normalizer_normalize' ) ) {
			if ( ! normalizer_is_normalized( $filename, Normalizer::FORM_C ) ) {
				$filename = normalizer_normalize( $filename, Normalizer::FORM_C );
			}
		}

		return $filename;
	}
}