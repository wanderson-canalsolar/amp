<?php
/**
 * Plugin Name:       Authors List
 * Description:       Display a list of post authors. <a href="https://wordpress.org/support/plugin/authors-list/">Need help? Submit support request.</a>
 * Version:           1.2.4
 * Author:            WPKube
 * Author URI:        http://wpkube.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       authors-list
 * Domain Path:       /languages
 */

// called directly, abort
if ( ! defined( 'WPINC' ) ) {
	die;
}

// constants
define( 'AUTHORS_LIST_VERSION', '1.2.4' );
define( 'AUTHORS_LIST_URL', plugin_dir_url( __FILE__ ) );
define( 'AUTHORS_LIST_BASENAME', plugin_basename( __FILE__ ) );
define( 'AUTHORS_LIST_DIR_NAME', dirname( plugin_basename( __FILE__ ) ) );
define( 'AUTHORS_LIST_ABS', dirname(__FILE__) );

// includes
include AUTHORS_LIST_ABS . '/includes/general.php';
include AUTHORS_LIST_ABS . '/includes/display.php';