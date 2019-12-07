<?php
/**
 * Setup and display the settings page data
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

use function add_action;
use function add_settings_field;
use function add_settings_section;
use function array_map;
use function dirname;
use function esc_attr;
use function is_array;
use function is_scalar;
use function register_setting;
use function sanitize_text_field;

/**
 * [VralleLazyLoadSettings description]
 */
class Settings_API {
	/**
	 * The single instance of this plugin.
	 *
	 * @see    Settings_API()
	 *
	 * @access private
	 * @var    Settings_API
	 */
	private static $instance;

	/**
	 * Constructor. Doesn't actually do anything as instance() creates the class instance.
	 */
	private function __construct() {}

	/**
	 * Prevents the class from being cloned.
	 */
	public function __clone() {
		wp_die( "Please don't clone Settings_API" );
	}

	/**
	 * Prints the class from being unserialized and woken up.
	 */
	public function __wakeup() {
		wp_die( "Please don't unserialize/wakeup Settings_API" );
	}

	/**
	 * Creates a new instance of this class if one hasn't already been made
	 * and then returns the single instance of this class.
	 *
	 * @return Settings_API
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Settings_API();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Register all of the needed hooks and actions.
	 */
	public function init() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'add_settings_sections' ) );
		add_action( 'admin_init', array( $this, 'add_settings_fields' ) );
	}

	/**
	 * Register the plugin settings
	 */
	public function register_settings() {
		$settings_name    = get_settings_page_slug();
		$settings_group   = get_settings_group();
		$default_settings = get_default_settings();

		$args = array(
			'sanitize_callback' => array( $this, 'sanitize_settings' ),
			'default'           => $default_settings,
		);

		register_setting(
			$settings_group,
			$settings_name,
			$args
		);
	}

	/**
	 * Validating sanitizing and escaping settings data
	 *
	 * @param  mixed $settings data to sanitize.
	 * @return mixed Sanitized data.
	 */
	public function sanitize_settings( $settings ) {
		$_settings = array();

		foreach ( $settings as $key => $value ) {
			if ( 'skip_class_names' === $key ) {
				$value = str_replace( array( '.', ',' ), ' ', $value );

				$class_names = explode( ' ', $value );
				$class_names = array_map( 'sanitize_html_class', $class_names );

				$value = implode( ' ', $class_names );
			}

			if ( is_array( $value ) ) {
				$_settings[ $key ] = array_map( 'sanitize_text_field', $value );
			} elseif ( is_scalar( $value ) ) {
				$_settings[ $key ] = sanitize_text_field( $value );
			}
		}

		return $_settings;
	}

	/**
	 * Add sections to the plugin settings page
	 */
	public function add_settings_sections() {
		$callback = array( $this, 'sections_callback' );
		$page     = get_settings_page_slug();

		$sections = get_settings_sections();
		foreach ( $sections as $section ) {
			add_settings_section(
				$section['id'],
				$section['title'],
				$callback,
				$page
			);
		}
	}

	/**
	 * Output section description
	 *
	 * @param array $arg Section data.
	 */
	public function sections_callback( $arg ) {}

	/**
	 * Add fields to the plugin settings page
	 */
	public function add_settings_fields() {
		$page   = get_settings_page_slug();
		$config = get_settings_config();
		foreach ( $config as $field ) {
			if ( empty( $field['id'] ) ) {
				continue;
			}
			$field['class'] = 'vll-input-line vll-input-line--type-' . $field['type'] . ' vll-input-line--section-' . $field['section'];
			add_settings_field(
				esc_attr( $field['id'] ), // Field ID.
				isset( $field['title'] ) ? $field['title'] : '', // Field Title.
				array( $this, 'field_callback' ), // Callback for render Input.
				$page, // Page slug.
				isset( $field['section'] ) ? $field['section'] : '', // Section ID.
				$field // Args for callback.
			);
		}
	}

	/**
	 *  Output the settings fields
	 *
	 * @param  array $args field configuration data.
	 */
	public function field_callback( $args ) {
		$settings_name = get_settings_name();
		$settings      = get_settings(); // phpcs:ignore WordPress.WP.DeprecatedFunctions.get_settingsFound
		$id            = $args['id'];

		switch ( $args['type'] ) {
			case 'checkbox':
				require dirname( __FILE__ ) . '/views/fields/checkbox.php';
				break;
			case 'number':
				require dirname( __FILE__ ) . '/views/fields/number.php';
				break;
			case 'select':
				require dirname( __FILE__ ) . '/views/fields/select.php';
				break;
			case 'text':
				require dirname( __FILE__ ) . '/views/fields/text.php';
				break;
			default:
				return '';
		}
	}

	/**
	 * Adds an error message to WordPress' error collection to be displayed in the dashboard.
	 *
	 * @param string $message_id Slug-name to identify the error. Used as part of 'id' attribute in HTML output.
	 * @param string $message    The formatted message text to display to the user.
	 * @param string $type       Message type, controls HTML class. Accepts 'error' or 'updated'.
	 */
	public function add_error( $message_id, $message, $type = 'error' ) {
		$errors_slug = get_settings_error_slug();

		add_settings_error(
			$errors_slug,
			$message_id,
			$message,
			$type
		);
	}
}
