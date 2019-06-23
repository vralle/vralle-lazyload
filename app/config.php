<?php
namespace Vralle\Lazyload\App;

/**
 * A class wrapper for handling configuration and options of the plugin
 *
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app
 */
class Config {
	/**
	 * The configuration of this plugin
	 *
	 * @var array
	 */
	protected $plugin_config;

	/**
	 * The options of the plugin
	 *
	 * @var array
	 */
	protected $plugin_options;

	/**
	 * Initialize the configuration.
	 *
	 * @param string $plugin_name   The name of this plugin.
	 * @param array  $plugin_config The configuration of this plugin.
	 */
	public function __construct( $plugin_name, $plugin_config ) {
		$this->plugin_config  = $plugin_config;
		$this->plugin_options = $this->setupOptions( $plugin_name );
	}

	/**
	 * Setup and retrieve the options of the plugin
	 *
	 * @param string $plugin_name The plugin slug.
	 * @return array The options of the plugin.
	 */
	public function setupOptions( $plugin_name ) {
		$default_options  = $this->getDefaultOptions();
		$saved_options    = \get_option( $plugin_name, $default_options );
		$options          = array();
		$required_options = array(
			'css_exception',
			'data-expand',
			'loadmode',
			'object-fit',
		);

		foreach ( $default_options as $key => $value ) {
			if ( isset( $saved_options[ $key ] ) ) {
				$options[ $key ] = $saved_options[ $key ];
			} elseif ( in_array( $key, $required_options, true ) ) {
				$options[ $key ] = $value;
			}
		}

		return $options;
	}

	/**
	 * Retrieve the default options of the plugin
	 *
	 * @return array The default options of the plugin
	 */
	public function getDefaultOptions() {
		$default_options = array();
		foreach ( $this->plugin_config as $option ) {
			$default_options[ $option['uid'] ] = $option['default'];
		}
		return $default_options;
	}

	/**
	 * Retrieve the configuration of the plugin
	 *
	 * @return array The configuration of the plugin
	 */
	public function getConfig() {
		return $this->plugin_config;
	}

	/**
	 * Retrieve the options of the plugin
	 *
	 * @return array The options of the plugin
	 */
	public function getOptions() {
		return $this->plugin_options;
	}
}
