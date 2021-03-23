<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/vralle/vralle-lazyload
 * @package           vralle-lazyload
 *
 * @wordpress-plugin
 * Plugin Name:       vralle.lazyload
 * Plugin URI:        https://github.com/vralle/vralle-lazyload
 * Description:       Brings lazySizes.js to WordPress
 * Version:           1.1.0
 * Author:            V.Ralle
 * Author URI:        https://github.com/vralle
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       vralle-lazyload
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/vralle/vralle-lazyload.git
 * Requires WP:       4.9
 * Requires PHP:      7.1
 */

namespace VRalleLazyLoad;

\defined( 'ABSPATH' ) || exit;

use function \add_action;
use function \define;
use function \plugin_basename;
use function \plugin_dir_path;
use function \plugin_dir_url;

define( 'VLL_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'VLL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VLL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VLL_PLUGIN_VERSION', '1.1.0' );

/**
 * Load the plugin dependencies and settings
 *
 * @return void
 */
function load_plugin() {
	require_once __DIR__ . '/includes/class-base-settings.php';
	require_once __DIR__ . '/includes/class-lazyload.php';
	require_once __DIR__ . '/includes/class-frontend.php';

	BaseSettings::init();
}

/**
 * Init the plugin to frontend
 *
 * @return void
 */
function lazyload() {
	Frontend::init();
}

/**
 * Init the plugin to admin area
 *
 * @return void
 */
function admin_ui() {
	if ( is_admin() ) {
		require_once __DIR__ . '/includes/class-admin-ui.php';
		Admin_UI::init();
	}
}


/**
 * Init the plugin parts latter
 *
 * @return void
 */
function init() {
	load_plugin();
	add_action( 'wp', __NAMESPACE__ . '\\lazyload' );
	add_action( 'init', __NAMESPACE__ . '\\admin_ui' );
}

init();

/**
 * Remove the plugin option on uninstall.
 *
 * @return void
 */
function uninstall() {
	delete_option( 'vralle-lazyload' );
}

register_uninstall_hook( __FILE__, __NAMESPACE__ . '\\uninstall' );
