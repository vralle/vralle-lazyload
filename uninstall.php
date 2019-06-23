<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package vralle-lazyload
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete the options of the plugin
 */
delete_option( 'vralle_lazyload' );
