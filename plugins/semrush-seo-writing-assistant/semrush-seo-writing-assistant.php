<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * php version 5.2.4
 *
 * @category SemrushSwa
 * @package  SemrushSwa
 * @author   SEMrush CY LTD <apps@semrush.com>
 * @license  GPL-2.0+ <http://www.gnu.org/licenses/gpl-2.0.txt>
 * @link     https://www.semrush.com/
 * @since    1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:       Semrush SEO Writing Assistant
 * Plugin URI:        https://www.semrush.com/swa/
 * Description:       The Semrush SEO Writing Assistant provides instant recommendations for content optimization based on the best-performing articles in the Google top-10.
 * Version:           1.2.0
 * Author:            Semrush
 * Author URI:        https://www.semrush.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       semrush
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Current plugin version.
 */
define( 'SEMRUSH_SEO_WRITING_ASSISTANT_VERSION', '1.2.0' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since  1.0.0
 * @return void
 */
function run_semrush_swa() {
	/**
	 * Load and initialize the metabox plugin.
	 */
	include_once dirname( __FILE__ ) . '/admin/class-semrushswa-metabox.php';
	new SemrushSwa_MetaBox();
}

if ( is_admin() ) {
	add_action( 'plugins_loaded', 'run_semrush_swa', 10 );
}
