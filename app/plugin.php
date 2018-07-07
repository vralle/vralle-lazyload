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
    /**
     * The unique identifier of this plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The name of a plugin bootstrap file
     *
     * @since    0.8.0
     * @access   protected
     * @var      string    $plugin_basename    The name of a plugin bootstrap file
     */
    protected $plugin_basename;

    /**
     * The current version of the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    0.1.0
     * @access   protected
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    protected $options;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    0.1.0
     */
    public function __construct($plugin_basename)
    {
        if (defined('VRALLE_LAZYLOAD_VERSION')) {
            $this->version = VRALLE_LAZYLOAD_VERSION;
        } else {
            $this->version = '1.0.0';
        }

        $this->plugin_basename = $plugin_basename;
        $this->plugin_name = 'vralle_lazyload';

        $this->loadDependencies();
        $this->setLocale();
        $this->setupSettings();
        if (is_admin()) {
            $this->adminHooks();
        } else {
            $this->publicHooks();
        }
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    0.8.0
     * @access   private
     */
    private function loadDependencies()
    {
        $plugin_dir_path = \plugin_dir_path(dirname(__FILE__));

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once $plugin_dir_path . 'app/loader.php';

        require_once $plugin_dir_path . 'app/options.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once $plugin_dir_path . 'app/i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        if (is_admin()) {
            require_once $plugin_dir_path . 'app/admin.php';
        }

        /**
         * Retrieve the HTML tags regular expression for searching.
         */
        require_once $plugin_dir_path . 'app/util.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once $plugin_dir_path . 'app/lazysizes.php';

        /**
         * Custom template tags
         */
        require_once $plugin_dir_path . 'app/template-tags.php';

        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    0.8.0
     * @access   private
     */
    private function setLocale()
    {
        $plugin_i18n = new i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function setupSettings()
    {
        $plugin_settings = array(
            array(
                'uid'           => 'wp_images',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Image attachments', 'vralle-lazyload'),
                'label'         => \__('Lazy loading of images displayed by Wordpress methods.', 'vralle-lazyload'),
                'description'   => \__('For example, the Post Thumbnails and Featured Images. Default "Yes".', 'vralle-lazyload'),
                'section'       => 'images',
            ),
            array(
                'uid'           => 'custom_header',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Custom Header', 'vralle-lazyload'),
                'label'         => \__('Lazy loading of images in the Custom Header.', 'vralle-lazyload'),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'images',
            ),
            array(
                'uid'           => 'content_images',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Post content', 'vralle-lazyload'),
                'label'         => \__('Lazy loading of images in the Post Content.', 'vralle-lazyload'),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'images',
            ),
            array(
                'uid'           => 'avatar',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Avatar', 'vralle-lazyload'),
                'label'         => \__('Lazy loading the Avatars.', 'vralle-lazyload'),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'images',
            ),
            array(
                'uid'           => 'content_iframes',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Iframes', 'vralle-lazyload'),
                'label'         => \__('Lazy loading the iframes.', 'vralle-lazyload'),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'images',
            ),
            array(
                'uid'           => 'do_src',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Traditional images', 'vralle-lazyload'),
                'label'         => \sprintf(
                    \__('Lazy loading of images without the attribute "%s".', 'vralle-lazyload'),
                    'srcset'
                ),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'responsive',
            ),
            array(
                'uid'           => 'do_srcset',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Responsive images', 'vralle-lazyload'),
                'label'         => \sprintf(
                    \__('Lazy loading images with the attribute "%s".', 'vralle-lazyload'),
                    'srcset'
                ),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'responsive',
            ),
            array(
                'uid'           => 'data-sizes',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => 'Sizes',
                'label'         => \__('Calculate sizes automatically.', 'vralle-lazyload'),
                'description'   => \__('This will replace the data of sizes attribute. Default "Yes".', 'vralle-lazyload'),
                'section'       => 'responsive',
            ),
            array(
                'uid'           => 'exclude_class',
                'type'          => 'text',
                'default'       => '',
                'title'         => \__('CSS Class', 'vralle-lazyload'),
                'label'         => \__('CSS-classes of images that need to be excluded from lazy loading.', 'vralle-lazyload'),
                'description'   => \__('Space separated', 'vralle-lazyload'),
                'class'         => 'regular-text',
                'section'       => 'exclude',
            ),
            array(
                'uid'           => 'data-expand',
                'type'          => 'number',
                'default'       => '0',
                'title'         => 'Expand',
                'label'         => \sprintf(
                    \__('The "%s" option.', 'vralle-lazyload'),
                    'expand'
                ),
                'description'   => \__('Normally lazysizes will expand the viewport area to lazy preload images/iframes which might become visible soon. Default "0".', 'vralle-lazyload'),
                'section'       => 'fine_tuning',
            ),
            array(
                'uid'           => 'loadmode',
                'type'          => 'number',
                'default'       => '2',
                'min'           => '0',
                'max'           => '3',
                'step'          => '1',
                'title'         => 'loadMode',
                'label'         => \sprintf(
                    \__('The "%s" option.', 'vralle-lazyload'),
                    'loadMode'
                ),
                'description'   => \__('Possible values are 0 = don\'t load anything, 1 = only load visible elements, 2 = load also very near view elements (expand option) and 3 = load also not so near view elements (expand * expFactor option). This value is automatically set to 3 after onload. Change this value to 1 if you (also) optimize for the onload event or change it to 3 if your onload event is already heavily delayed. Default: 2.', 'vralle-lazyload'),
                'section'       => 'fine_tuning',
            ),
            array(
                'uid'           => 'preloadafterload',
                'type'          => 'checkbox',
                'default'       => null,
                'title'         => 'preloadafterload',
                'label'         => \__('Load all elements after the window onload event.', 'vralle-lazyload'),
                'description'   => \__('Whether lazysizes should load all elements after the window onload event. Note: lazySizes will then still download those not-in-view images inside of a lazy queue, so that other downloads after onload are not blocked. Default "No".', 'vralle-lazyload'),
                'section'       => 'fine_tuning',
            ),
            array(
                'uid'           => 'parent-fit',
                'type'          => 'checkbox',
                'default'       => null,
                'title'         => 'parent-fit',
                'label'         => \sprintf(
                    \__('Load %s extension. Requires "%s".', 'vralle-lazyload'),
                    'parent-fit',
                    'Sizes'
                ),
                'description'   => \__('The parent fit plugin extends the data-sizes="auto" feature to also calculate the right sizes for object-fit: contain|cover image elements as also height ( and width) constrained image elements in general. Default "No".', 'vralle-lazyload'),
                'section'       => 'extensions',
            ),
            array(
                'uid'           => 'object-fit',
                'type'          => 'select',
                'default'       => 'contain',
                'options'           => array(
                    'contain'           => 'contain',
                    'cover'             => 'cover',
                    'width'             => 'width',
                ),
                'title'         => \__('object-fit', 'vralle-lazyload'),
                'label'         => \sprintf(
                    \__('Select object-fit type. Requires "%s".', 'vralle-lazyload'),
                    'parent-fit'
                ),
                'description'   => \sprintf(
                    \__('Read more: %s. Default "%s".', 'vralle-lazyload'),
                    '<a href="https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/parent-fit">https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/parent-fit</a>',
                    'contain'
                ),
                'section'       => 'extensions',
            ),
        );

        $options = new Options($this->getPluginName(), $plugin_settings);

        $this->options = $options;
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    0.8.0
     * @access   private
     */
    private function adminHooks()
    {
        $admin = new Admin($this->getPluginName(), $this->getOptions());
        $this->loader->add_action('admin_menu', $admin, 'addAdminPage');
        $this->loader->add_action('plugin_action_links_' . $this->plugin_basename, $admin, 'addSettingsLink');
        $this->loader->add_action('admin_menu', $admin, 'registerSetting');
        $this->loader->add_action('admin_init', $admin, 'addSettingSections');
        $this->loader->add_action('admin_init', $admin, 'addSettingFields');
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    0.8.0
     * @access   private
     */
    private function publicHooks()
    {
        $lazysizes = new Lazysizes($this->getPluginName(), $this->getVersion(), $this->getOptions());
        $this->loader->add_filter('wp_get_attachment_image_attributes', $lazysizes, 'wpGetAttachmentImageAttributes', 99);
        $this->loader->add_filter('get_header_image_tag', $lazysizes, 'getHeaderImageTag', 99);
        $this->loader->add_filter('the_content', $lazysizes, 'theContent', 99);
        $this->loader->add_filter('get_avatar', $lazysizes, 'getAvatar', 99);
        $this->loader->add_action('wp_enqueue_scripts', $lazysizes, 'enqueueScripts', 1);
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
     * @since     0.8.0
     * @return    string    The name of the plugin.
     */
    public function getPluginName()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     0.8.0
     * @return    string    The version number of the plugin.
     */
    public function getVersion()
    {
        return $this->version;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
