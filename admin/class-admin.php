<?php
/**
 * Settings page of the Plugin
 *
 * @package vralle-lazyload
 */

namespace VRalleLazyLoad;

use function __;
use function add_action;
use function add_options_page;
use function apply_filters;
use function array_unshift;
use function checked;
use function defined;
use function dirname;
use function esc_attr;
use function esc_html;
use function esc_url;
use function filemtime;
use function get_admin_page_title;
use function menu_page_url;
use function plugins_url;
use function selected;
use function sprintf;
use function wp_enqueue_style;
use function wp_kses;

/**
 * Add and render the plugin settings page to WP Admin
 */
class Admin {
	/**
	 * The single instance of this plugin.
	 *
	 * @see    RegenerateThumbnails()
	 *
	 * @access private
	 * @var    Admin
	 */
	private static $instance;

	/**
	 * The menu ID of this plugin, as returned by add_management_page().
	 *
	 * @var string
	 */
	public $page_path;

	/**
	 * Constructor. Doesn't actually do anything as instance() creates the class instance.
	 */
	private function __construct() {}

	/**
	 * Prevents the class from being cloned.
	 */
	public function __clone() {
		wp_die( "Please don't clone Admin" );
	}

	/**
	 * Prints the class from being unserialized and woken up.
	 */
	public function __wakeup() {
		wp_die( "Please don't unserialize/wakeup Admin" );
	}

	/**
	 * Creates a new instance of this class if one hasn't already been made
	 * and then returns the single instance of this class.
	 *
	 * @return Admin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Admin();
			self::$instance->init();
		}
		return self::$instance;
	}

	/**
	 * Register all of the needed hooks and actions.
	 */
	public function init() {
		// Add a new item to the Tools menu in the admin menu.
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		// Load the required JavaScript and CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueues' ) );
		add_action( 'plugin_action_links_' . VLL_PLUGIN_BASENAME, array( $this, 'add_action_link' ) );
	}

	/**
	 * Add the settings page to admin menu
	 */
	public function add_settings_page() {
		$page_title = 'VRALLE.Lazyload';
		$menu_title = $page_title;
		// Allow people to change what capability is required to use this plugin.
		$capability = apply_filters( 'vll_settings_capability', 'manage_options' );
		$page       = get_settings_page_slug();
		$callback   = array( $this, 'render_settings_page' );

		$page_path = add_options_page(
			$page_title,
			$menu_title,
			$capability,
			$page,
			$callback
		);

		$this->page_path = $page_path;
	}

	/**
	 * Display the settings page
	 */
	public function render_settings_page() {
		$page_tile      = get_admin_page_title();
		$error_slug     = get_settings_error_slug();
		$settings_group = get_settings_group();
		$settings_page  = get_settings_page_slug();
		require dirname( __FILE__ ) . '/views/admin-page.php';
	}

	/**
	 * Enqueues the requires stylesheet on the plugin's admin page.
	 *
	 * @param string $hook_suffix The current page's hook suffix as provided by admin-header.php.
	 */
	public function admin_enqueues( $hook_suffix ) {
		$page_path = $this->page_path;

		if ( $hook_suffix !== $page_path ) {
			return;
		}

		wp_enqueue_style(
			'vll-settings-page',
			plugins_url( '/css/vll-settings-page.css', __FILE__ ),
			array(),
			( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? filemtime( dirname( __FILE__ ) . '/css/vll-settings-page.css' ) : ''
		);
	}

	/**
	 * Add an action link displayed for the plugin in the Plugins list table.
	 *
	 * @param array $actions An array of plugin action links.
	 */
	public function add_action_link( $actions ) {
		$settings_page     = get_settings_page_slug();
		$settings_page_url = menu_page_url( $settings_page, false );
		$settings_link     = sprintf(
			'<a href="%s">%s</a>',
			esc_url( $settings_page_url ),
			__( 'Settings', 'vralle-lazyload' )
		);
		array_unshift( $actions, $settings_link );

		return $actions;
	}
}
