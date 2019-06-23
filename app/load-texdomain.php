<?php
namespace Vralle\Lazyload\App;

/**
 * Define the internationalization functionality
 *
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app
 */
class LoadTexdomain {
	/**
	 * Load the plugin text domain for translation
	 */
	public function loadPluginTextdomain() {
		\load_plugin_textdomain(
			'vralle-lazyload',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
