<?php
/**
 * Helper methods.
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

/**
 * Retrieve plugin settings page slug
 *
 * @return string The slug name to refer to plugin settings page
 */
function get_settings_page_slug() {
	$page_slug = 'vralle-lazyload';
	return $page_slug;
}

/**
 * Retrieve plugin option name
 *
 * @return string Plugin option name
 */
function get_settings_name() {
	$settings_name = 'vralle-lazyload';

	return $settings_name;
}

/**
 * Retrieve the name of the plugin settings group
 *
 * @return string A settings group name
 */
function get_settings_group() {
	$settings_group = get_settings_page_slug();

	return $settings_group;
}

/**
 * Retrieve the slug of the plugin errors
 *
 * @return string The plugin errors slug
 */
function get_settings_error_slug() {
	$errors_slug = get_settings_page_slug();

	return $errors_slug;
}

/**
 * Default values of the plugin settings
 *
 * @return array Names of settings and their default values
 */
function get_default_settings() {
	$default_settings = array();
	$plugin_config    = get_settings_config();
	foreach ( $plugin_config as $option ) {
		if ( ! isset( $option['id'] ) ) {
			continue;
		}
		if ( isset( $option['default'] ) ) {
			$default_settings[ $option['id'] ] = $option['default'];
		} else {
			$default_settings[ $option['id'] ] = null;
		}
	}

	return $default_settings;
}

/**
 * Retrieve the plugin settings
 *
 * @return array Names of settings and their values
 */
function get_settings() {
	$settings_name    = get_settings_name();
	$default_settings = get_default_settings();
	$settings         = \get_option( $settings_name, $default_settings );

	return $settings;
}

/**
 * Retrieve an option value
 *
 * @param string $name Name of option to retrieve.
 * @return mixed Value set for the option.
 */
function get_option( $name ) {
	$settings = get_settings(); // phpcs:ignore WordPress.WP.DeprecatedFunctions.get_settingsFound
	$option   = isset( $settings[ $name ] ) ? $settings[ $name ] : null;

	return $option;
}

/**
 * State detection to skip processing
 *
 * @return boolean
 */
function is_exit() {
	/**
	 * Exit filter
	 *
	 * @param boolean
	 */
	if ( ! apply_filters( 'do_vralle_lazyload', true ) ) {
		return true;
	}

	if ( is_admin() ) {
		return true;
	}

	// Feed.
	if ( is_feed() ) {
		return true;
	}

	// On an AMP version of the posts.
	if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
		return true;
	}

	// Preview mode.
	if ( is_preview() ) {
		return true;
	}

	if ( is_customize_preview() ) {
		return true;
	}

	// Print.
	if ( 1 === intval( get_query_var( 'print' ) ) ) {
		return true;
	}

	// Print.
	if ( 1 === intval( get_query_var( 'printpage' ) ) ) {
		return true;
	}

	return false;
}
