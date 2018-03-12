<?php
namespace Vralle\Lazyload\App;

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.1.0
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 */
class Plugin
{
    private $plugin_dir_path;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    0.1.0
     */
    public function __construct()
    {
        if (defined('VRALLE_LAZYLOAD_VERSION')) {
            $this->version = VRALLE_LAZYLOAD_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'vralle-lazyload';
        $this->plugin_dir_path = \plugin_dir_path(dirname(__FILE__));

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - Loader. Orchestrates the hooks of the plugin.
     * - i18n. Defines internationalization functionality.
     * - Admin_Setup. Defines all hooks for the admin area.
     * - Frontend_Setup. Defines all hooks for the public side of the site.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    0.1.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once $this->plugin_dir_path . 'app/loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once $this->plugin_dir_path . 'app/i18n.php';

        /**
         * Retrieve the HTML tags regular expression for searching.
         */
        require_once $this->plugin_dir_path . 'app/service.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once $this->plugin_dir_path . 'app/settings.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once $this->plugin_dir_path . 'app/lazysizes.php';

        /**
         * Custom template tags
         */
        require_once $this->plugin_dir_path . 'app/template-tags.php';

        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    0.1.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $settings = new Settings($this->get_plugin_name(), $this->get_version());
        $this->loader->add_action('admin_menu', $settings, 'register_settings_page');
        $this->loader->add_action('admin_init', $settings, 'define_fields_config');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    0.1.0
     * @access   private
     */
    private function define_public_hooks()
    {
        $lazysizes = new Lazysizes($this->get_plugin_name(), $this->get_version());
        $this->loader->add_filter('wp_get_attachment_image_attributes', $lazysizes, 'wp_get_attachment_image_attributes', 99);
        $this->loader->add_filter('get_header_image_tag', $lazysizes, 'get_header_image_tag', 99);
        $this->loader->add_filter('the_content', $lazysizes, 'the_content', 99);
        $this->loader->add_filter('get_avatar', $lazysizes, 'get_avatar', 99);
        $this->loader->add_action('wp_enqueue_scripts', $lazysizes, 'enqueue_scripts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    0.1.0
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     0.1.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     0.1.0
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.1.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }
}
