<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

use function defined;
use function delete_option;

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete the options of the plugin
 */

$settings_name = get_settings_name();
delete_option( $settings_name );
