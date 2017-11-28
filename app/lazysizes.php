<?php
namespace Vralle\Lazyload\App;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://author.uri
 * @since      0.1.0
 *
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 * @author     Vitaliy Ralle <email4vit@gmail.com>
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
        $this->debug_suffix = (defined('SCRIPT_DEBUG')) ? '.js' : '.min.js';
        $this->options = get_option(Settings::PLUGIN_OPTION['id'], Settings::PLUGIN_OPTION['default']);
        $this->lazysizes_dir_url = trailingslashit(plugin_dir_url(dirname(__FILE__)) . 'vendor/lazysizes');
    }

    private function is_exit()
    {
        if (is_admin()) {
            return true;
        }

        if (is_feed()) {
            return true;
        }

        if (is_preview()) {
            return true;
        }

        if (1 === intval(get_query_var('print'))) {
            return true;
        }

        if (1 === intval(get_query_var('printpage'))) {
            return true;
        }

        return false;
    }

    /**
     * Callback for Filter of attachment images.
     *
     * @param  array $attr_arr List Attributes of the image.
     * @return array List of attachment image attributes
     */
    public function do_wp_images_lazy($attr_arr)
    {
        if (!absint($this->options['wp_images'])) {
            return $attr_arr;
        }

        if ($this->is_exit()) {
            return $attr_arr;
        }

        if (empty($attr_arr)) {
            return $attr_arr;
        }

        if (isset($attr_arr['class'])) {
            $classes_arr = explode(' ', $attr_arr['class']);

            if (in_array(self::LAZY_CLASS, $classes_arr)) {
                return $attr_arr;
            }


            if (!empty($this->options['exclude_class'])) {
                $exlude_class_arr = array_map('trim', explode(' ', $this->options['exclude_class']));
                if (!empty(array_intersect($exlude_class_arr, $classes_arr))) {
                    return $attr_arr;
                }
            }

            $classes_arr[] = self::LAZY_CLASS;
            $attr_arr['class'] = implode(' ', $classes_arr);
        }

        if (isset($attr_arr['src'])) {
            if (isset($attr_arr['srcset'])) {
                if (isset($this->options['do_srcset']) && !!absint($this->options['do_srcset'])) {
                    $attr_arr['data-srcset'] = $attr_arr['srcset'];
                    $attr_arr['srcset'] = apply_filters('vralle_lazyload_placeholder_image', self::IMG_PLACEHOLDER);
                }
                if (isset($this->options['data-sizes']) && !!absint($this->options['data-sizes'])) {
                    $attr_arr['data-sizes'] = 'auto';
                    unset($attr_arr['sizes']);
                }
            } else {
                if (isset($this->options['do_src']) && !!absint($this->options['do_src'])) {
                    $attr_arr['data-src'] = $attr_arr['src'];
                    $attr_arr['src'] = apply_filters('vralle_lazyload_placeholder_image', self::IMG_PLACEHOLDER);
                }
            }
        }

        return apply_filters('vralle_lazyload_attr_output', $attr_arr);
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
        wp_enqueue_script(
            $this->plugin_name . '_lazysizes',
            $this->lazysizes_dir_url . 'lazysizes' . $this->debug_suffix,
            array(),
            $this->version,
            true
        );
    }
}
