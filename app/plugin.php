<?php
namespace Vralle\Lazyload\App;

/**
 * A class that defines core functionality of the plugin
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app
 */
class Plugin
{
    /**
     * The unique identifier of this plugin.
     * @var string
     */
    protected $plugin_name;

    /**
     * Path to the plugin bootstrap file relative to the plugins directory
     * @var string
     */
    protected $plugin_basename;

    /**
     * The current version of the plugin.
     * @var string
     */
    protected $version;

    /**
     * The loader that's responsible for maintaining and registering all hooks that power the plugin.
     * @var object
     */
    protected $loader;

    /**
     * The settings functionality of this plugin
     * @var object
     */
    protected $config;

    /**
     * Define the core functionality of the plugin.
     * @param string $plugin_basename Path to the plugin bootstrap file relative to the plugins directory.
     */
    public function __construct($plugin_basename)
    {
        if (defined('VRALLE_LAZYLOAD_VERSION')) {
            $this->version = VRALLE_LAZYLOAD_VERSION;
        } else {
            $this->version = '0.9.2';
        }

        $this->plugin_basename = $plugin_basename;
        $this->plugin_name = 'vralle_lazyload';

        $this->loadDependencies();
        $this->setLocale();
        $this->configSetup();
        if (is_admin()) {
            $this->adminHooks();
        } else {
            $this->publicHooks();
        }
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function loadDependencies()
    {
        $plugin_dir_path = \plugin_dir_path(dirname(__FILE__));

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once $plugin_dir_path . 'app/loader.php';

        /**
         * The class responsible for defining internationalization functionality of the plugin.
         */
        require_once $plugin_dir_path . 'app/i18n.php';

        /**
         * The class responsible for configuration and options of the plugin.
         */
        require_once $plugin_dir_path . 'app/config.php';

        if (is_admin()) {
            /**
             * The class responsible for defining all actions that occur in the admin area.
             */
            require_once $plugin_dir_path . 'app/admin.php';
        } else {
            /**
             * The class responsible for defining all actions that occur in the public-facing
             * side of the site.
             */
            require_once $plugin_dir_path . 'app/lazysizes.php';

            /**
             * Custom template tags
             */
            require_once $plugin_dir_path . 'app/template-tags.php';
        }

        $this->loader = new Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function setLocale()
    {
        $plugin_i18n = new i18n($this->getPluginBasename());
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Define plugin configuration
     */
    private function configSetup()
    {
        $plugin_config = array(
            array(
                'uid'           => 'wp_images',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Image attachments', 'vralle-lazyload'),
                'label'         => \__('Lazy loading of images displayed by Wordpress methods.', 'vralle-lazyload'),
                'description'   => \__('For example, the Post Thumbnails, Featured Images and Logo in Custom Header. Default "Yes".', 'vralle-lazyload'),
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
                'uid'           => 'content_embed',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Embedded', 'vralle-lazyload'),
                'label'         => \__('Lazy loading the embedded, like iframe, embed, object and video', 'vralle-lazyload'),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'images',
            ),
            array(
                'uid'           => 'picturefill',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Load picturefill.js', 'vralle-lazyload'),
                'label'         => \__('Support for responsive images in older browsers, like IE 10, 11.', 'vralle-lazyload'),
                'description'   => \__('Default "Yes".', 'vralle-lazyload'),
                'section'       => 'responsive',
            ),
            array(
                'uid'           => 'do_src',
                'type'          => 'checkbox',
                'default'       => '1',
                'title'         => \__('Traditional images', 'vralle-lazyload'),
                'label'         => \sprintf(
                    /* translators: %s: srcset */
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
                    /* translators: %s: srcset */
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
                'label'         => \__('The name of CSS-class for images that need to be excluded from lazy loading.', 'vralle-lazyload'),
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
                    /* translators: %s: option name */
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
                    /* translators: %s: option name */
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
                    /* translators: 1: parent-fit, 2: Sizes */
                    \__('Load %1$s extension. Requires "%2$s".', 'vralle-lazyload'),
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
                    /* translators: %s: parent-fit */
                    \__('Select object-fit type. Requires "%s".', 'vralle-lazyload'),
                    'parent-fit'
                ),
                'description'   => \sprintf(
                    /* translators: 1: link, 2: contain */
                    \__('Read more: %1$s. Default "%2$s".', 'vralle-lazyload'),
                    '<a href="https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/parent-fit">https://github.com/aFarkas/lazysizes/tree/gh-pages/plugins/parent-fit</a>',
                    'contain'
                ),
                'section'       => 'extensions',
            ),
        );

        $this->config = new Config($this->getPluginName(), $plugin_config);
    }

    /**
     * Register all of the hooks related to the admin area functionality of the plugin.
     */
    private function adminHooks()
    {
        $admin = new Admin($this->getPluginName(), $this->getConfig());
        $this->loader->add_action('admin_menu', $admin, 'addAdminPage');
        $this->loader->add_action('plugin_action_links_' . $this->plugin_basename, $admin, 'addSettingsLink');
        $this->loader->add_action('admin_menu', $admin, 'registerSetting');
        $this->loader->add_action('admin_init', $admin, 'addSettingSections');
        $this->loader->add_action('admin_init', $admin, 'addSettingFields');
    }

    /**
     * Register all of the hooks related to the public-facing functionality of the plugin.
     */
    private function publicHooks()
    {
        $lazysizes = new Lazysizes($this->getPluginName(), $this->getVersion(), $this->getOptions());
        $this->loader->add_filter('wp_get_attachment_image_attributes', $lazysizes, 'wpGetAttachmentImageAttributes', 99);
        $this->loader->add_filter('the_content', $lazysizes, 'theContent', 99);
        $this->loader->add_filter('get_avatar', $lazysizes, 'getAvatar', 99);
        $this->loader->add_action('wp_enqueue_scripts', $lazysizes, 'enqueueScripts', 1);
        $this->loader->add_action('wp_head', $lazysizes, 'addPicturefill');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->loader->run();
    }

    /**
     * Retrieve the name of the plugin
     * @return string The name of the plugin.
     */
    public function getPluginName()
    {
        return $this->plugin_name;
    }

    /**
     * Retrieve the version number of the plugin.
     * @return string The version number of the plugin.
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * The reference to the class for configuration and options of the plugin.
     * @return object Options
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Retrieve the options of the plugin.
     * @return array the options of the plugin.
     */
    public function getOptions()
    {
        return $this->config->getOptions();
    }

    /**
     * Retrieve relative path to the plugin bootstrap file
     * @return string path to the plugin bootstrap file relative to the plugins directory
     */
    public function getPluginBasename()
    {
        return $this->plugin_basename;
    }
}
