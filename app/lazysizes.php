<?php namespace Vralle\Lazyload\App;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.1.0
 *
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 */
class Lazysizes
{
    const LAZY_CLASS = 'lazyload';
    const IMG_PLACEHOLDER  = 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==';
    private $options;
    private $lazysizes_dir_url;


    /**
     * The ID of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    0.1.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * If we're debugging mode, define the
     * suffix for the file to load the proper version.
    */
    private $debug_suffix;

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->debug_suffix = \SCRIPT_DEBUG ? '' : '.min';
        $this->options = \get_option(Settings::PLUGIN_OPTION['id'], Settings::PLUGIN_OPTION['default']);
        $this->lazysizes_dir_url = \trailingslashit(\plugin_dir_url(dirname(__FILE__)) . 'vendor/lazysizes');
    }

    /**
     * Callback for Filter of attachment images.
     * @since    0.1.0
     *
     * @param  array $attr_arr List Attributes of the image.
     * @return array List of attachment image attributes
     */
    public function wp_attachment_image_attributes($attr_arr)
    {
        if (!\absint($this->options['wp_images'])) {
            return $attr_arr;
        }

        if ($this->is_exit()) {
            return $attr_arr;
        }

        $attr_arr = $this->attr_handler($attr_arr);

        return \apply_filters('vralle_lazyload_img_attr_output', $attr_arr);
    }



    /**
     * Filters the content.
     *
     * @since    0.6.0
     * @param   string $content
     * @return  string $content
     */
    public function the_content($content)
    {
        if ($this->is_exit()) {
            return $content;
        }

        $pattern = Service::get_tag_regex('img');

        return \preg_replace_callback(
            "/$pattern/i",
            function ($m) {
                // The wordpress handler is used. This may seem redundant, but it is reliable and verified
                // @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
                $parced = \shortcode_parse_atts($m[1]);

                $attr_arr = $this->attr_handler($parced);
                if ($parced['class'] == $attr_arr['class']) {
                    return $m[0];
                }
                $attr_arr = \apply_filters('vralle_lazyload_img_attr_output', $attr_arr);
                if (!empty($attr_arr)) {
                    $attr = '';
                    foreach ($attr_arr as $key => $value) {
                        $attr .= sprintf(' %s="%s"', $key, $value);
                    }
                    $tag = \str_replace($m[1], $attr, $m[0]);
                    return $tag;
                }
                return $m[0];
            },
            $content
        );
    }

    /**
     * State detection to skip processing if necessary
     * @since 0.1.0
     * @return boolean
     */
    private function is_exit()
    {
        if (\is_admin()) {
            return true;
        }

        if (\is_feed()) {
            return true;
        }

        if (\is_preview()) {
            return true;
        }

        if (1 === \intval(\get_query_var('print'))) {
            return true;
        }

        if (1 === \intval(\get_query_var('printpage'))) {
            return true;
        }

        return false;
    }

    /**
     * Image attribute handler
     * @since    0.6.0
     * @param  array     $attr_arr List of image attributes and their values.
     * @return array    List of image attributes and their values,
     *                   where the necessary attributes for the loader are added.
     */
    public function attr_handler($attr_arr)
    {
        if (empty($attr_arr)) {
            return $attr_arr;
        }

        if (isset($attr_arr['class'])) {
            $classes_arr = explode(' ', $attr_arr['class']);

            if (\in_array(self::LAZY_CLASS, $classes_arr)) {
                return $attr_arr;
            }


            if (!empty($this->options['exclude_class'])) {
                $exlude_class_arr = \array_map('trim', \explode(' ', $this->options['exclude_class']));
                if (!empty(\array_intersect($exlude_class_arr, $classes_arr))) {
                    return $attr_arr;
                }
            }

            $classes_arr[] = self::LAZY_CLASS;
            $attr_arr['class'] = \implode(' ', $classes_arr);
        } else {
            $attr_arr['class'] = self::LAZY_CLASS;
        }

        if (isset($attr_arr['src'])) {
            if (isset($attr_arr['srcset'])) {
                if (isset($this->options['do_srcset']) && !!\absint($this->options['do_srcset'])) {
                    $attr_arr['data-srcset'] = $attr_arr['srcset'];
                    $attr_arr['srcset'] = \apply_filters('vralle_lazyload_placeholder_image', self::IMG_PLACEHOLDER);
                }
                if (isset($this->options['data-sizes']) && !!\absint($this->options['data-sizes'])) {
                    $attr_arr['data-sizes'] = 'auto';
                    unset($attr_arr['sizes']);
                }
            } else {
                if (isset($this->options['do_src']) && !!\absint($this->options['do_src'])) {
                    $attr_arr['data-src'] = $attr_arr['src'];
                    $attr_arr['src'] = \apply_filters('vralle_lazyload_placeholder_image', self::IMG_PLACEHOLDER);
                    // remove sizes
                    unset($attr_arr['sizes']);
                }
            }
        }

        return $attr_arr;
    }

    /**
     * Create a list of lazysize.js plug-ins for the call
     * @since    0.6.0
     * @return array List of plugins
     */
    private function get_plugins_list()
    {
        $is_valid = array();

        $plugins = Settings::LS_PLUGINS;

        foreach ($this->options as $key => $value) {
            if (false !== \array_key_exists($key, $plugins) && $value > 0) {
                $is_valid[] = $key;
            }
        }

        return $is_valid;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    0.1.0
     */
    public function enqueue_scripts()
    {
        /**
         * This function is provided for demonstration purposes only.
         *
         * An instance of this class should be passed to the run() function
         * defined in Loader as all of the hooks are defined
         * in that particular class.
         *
         * The Loader will then create the relationship
         * between the defined hooks and the functions defined in this
         * class.
         */
        \wp_register_script(
            $this->plugin_name . '_lazysizes',
            $this->lazysizes_dir_url . 'lazysizes' . $this->debug_suffix . '.js',
            array(),
            $this->version,
            true
        );

        $plugins = $this->get_plugins_list();

        if (empty($plugins)) {
            \wp_enqueue_script($this->plugin_name . '_lazysizes');
        } else {
            foreach ($plugins as $plugin) {
                \wp_enqueue_script(
                    $this->plugin_name . '_ls.' . $plugin,
                    $this->lazysizes_dir_url . 'plugins/' . $plugin . '/ls.' . $plugin . $this->debug_suffix . '.js',
                    array($this->plugin_name . '_lazysizes'),
                    $this->version,
                    true
                );
            }
        }
    }
}
