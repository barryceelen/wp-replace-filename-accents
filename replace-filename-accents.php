<?php
/**
 * Main plugin file
 *
 * @package    WordPress
 * @subpackage ReplaceFilenameAccents
 * @author     Barry Ceelen
 * @license    GPL-3.0+
 * @link       https://github.com/barryceelen/wp-replace-filename-accents
 * @copyright  Barry Ceelen
 *
 * Plugin Name: Replace Filename Accents
 * Plugin URI: https://github.com/barryceelen/wp-replace-filename-accents
 * Description: Retroactively replace accented characters in your existing attachment file names.
 * Author: Barry Ceelen
 * Version: 0.0.1
 * Author URI: https://github.com/barryceelen
 * License: GPL3+
 * Text Domain: replace-filename-accents
 */

if ( is_admin() ) {
	require( 'class-replace-filename-accents.php' );
	add_action( 'plugins_loaded', array( 'ReplaceFilenameAccents', 'get_instance' ) );
}