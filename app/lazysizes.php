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
        $this->options = $this->get_option();
        $this->lazysizes_dir_url = \trailingslashit(\plugin_dir_url(dirname(__FILE__)) . 'vendor/lazysizes');
    }

    /**
     * Get default option values
     *
     * @since 0.7.1
     * @return array default option values
     */
    private function get_default()
    {
        $default = Settings::PLUGIN_OPTION['default'];
        $values = array();
        foreach ($default as $key => $data) {
            if (isset($data['value'])) {
                $values[$key] = $data['value'];
            } else {
                $values[$key] = '';
            }
        }
        return $values;
    }

    /**
     * Get option and their values
     *
     * @since 0.7.2
     * @return array option values
     */
    private function get_option()
    {
        $default = $this->get_default();
        $options = \get_option(Settings::PLUGIN_OPTION['id'], $default);
        $values = array();
        foreach ($default as $key => $value) {
            if (array_key_exists($key, $options)) {
                $values[$key] = $options[$key];
            } else {
                $values[$key] = $default[$key];
            }
        }
        return $values;
    }

    /**
     * Filter the list of attachment image attributes.
     * @link https://developer.wordpress.org/reference/hooks/wp_get_attachment_image_attributes/
     *
     * @since 2.8.0
     *
     * @param array        $attr_arr   Attributes for the image markup.
     */
    public function wp_get_attachment_image_attributes($attr_arr)
    {
        if ('1' !== $this->options['wp_images']) {
            return $attr_arr;
        }

        if ($this->is_exit()) {
            return $attr_arr;
        }

        $new_attr = $this->attr_handler($attr_arr);

        if ($new_attr) {
            $attr_arr = $new_attr;
        }

        return $attr_arr;
    }


    /**
     * Filter the markup of header images.
     * @link https://developer.wordpress.org/reference/hooks/get_header_image_tag/
     *
     * @since 0.7.1
     *
     * @param string $html      The HTML image tag markup being filtered.
     * @return string           The HTML image tag markup being filtered.
     */
    public function get_header_image_tag($html)
    {
        if ('1' !== $this->options['custom_header']) {
            return $html;
        }

        if ($this->is_exit()) {
            return $html;
        }

        $html = $this->content_handler($html);

        return $html;
    }

    /**
     * Filter the avatar to retrieve.
     * @link https://developer.wordpress.org/reference/hooks/get_avatar/
     *
     * @since 0.7.1
     *
     * @param string $html      <img> tag for the user's avatar.
     */
    public function get_avatar($html)
    {
        if ('1' !== $this->options['avatar']) {
            return $html;
        }

        if ($this->is_exit()) {
            return $html;
        }

        $html = $this->content_handler($html);

        return $html;
    }

    /**
     * Filter the post content.
     * @link https://developer.wordpress.org/reference/hooks/the_content/
     *
     * @since    0.6.0
     * @param   string $html Content of the current post.
     * @return  string       Content of the current post.
     */
    public function the_content($html)
    {
        if ($this->is_exit()) {
            return $html;
        }

        if ('1' !== $this->options['content_images']) {
            return $html;
        }

        $html = $this->content_handler($html);

        return $html;
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

    private function content_handler($html)
    {
        $pattern = Service::get_tag_regex('img');

        return \preg_replace_callback(
            "/$pattern/i",
            function ($m) {
                /**
                 * The wordpress handler is used. This may seem redundant, but it is reliable and verified
                 * @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
                 */
                $parced = \shortcode_parse_atts($m[1]);

                $attr_arr = $this->attr_handler($parced);

                if ($attr_arr) {
                    $attr = '';
                    foreach ($attr_arr as $key => $value) {
                        $attr .= sprintf(' %s="%s"', $key, $value);
                    }
                    $m[0] = \str_replace($m[1], $attr, $m[0]);
                }

                return $m[0];
            },
            $html
        );
    }

    /**
     * Image attribute handler
     * @since    0.6.0
     * @param  array  $attr_arr List of image attributes and their values.
     * @return array            List of image attributes and their values,
     *                          where the necessary attributes for the loader are added
     *                          or false, if exclude
     */
    private function attr_handler($attr_arr)
    {
        $lazy_class = apply_filters('vralle_lazyload_lazy_class', self::LAZY_CLASS);
        $image_placeholder = apply_filters('vralle_lazyload_image_placeholder', self::IMG_PLACEHOLDER);
        $classes_arr = array();
        $have_src = false;
        $exlude_class_arr = \array_map('trim', \explode(' ', $this->options['exclude_class']));

        // Exit by CSS class
        if (isset($attr_arr['class'])) {
            $classes_arr = explode(' ', $attr_arr['class']);

            if (!empty(\array_intersect($exlude_class_arr, $classes_arr))) {
                return false;
            }

            if (false !== \array_search($lazy_class, $classes_arr)) {
                return false;
            }
        }

        if (isset($attr_arr['srcset'])) {
            if ('1' === $this->options['do_srcset']) {
                $attr_arr['data-srcset'] = $attr_arr['srcset'];
                $attr_arr['srcset'] = $image_placeholder;
                $have_src = true;

                if ('1' === $this->options['data-sizes']) {
                    $attr_arr['data-sizes'] = 'auto';
                    unset($attr_arr['sizes']);
                }
            }
        } elseif (isset($attr_arr['src'])) {
            if ('1' === $this->options['do_src']) {
                $attr_arr['data-src'] = $attr_arr['src'];
                $attr_arr['src'] = $image_placeholder;
                $have_src = true;
            }
        }

        // Do lazyloaded, only if attributes have src or srcset
        if ($have_src) {
            $classes_arr[] = $lazy_class;
            $attr_arr['class'] = implode(' ', $classes_arr);
            if ('0' != $this->options['data-expand']) {
                $attr_arr['data-expand'] = $this->options['data-expand'];
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
            if (false !== \array_search($key, $plugins) && '1' === $value) {
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

        $lazySizesConfig = 'window.lazySizesConfig = window.lazySizesConfig || {};';
        if ($this->options['loadmode']) {
            $lazySizesConfig .= 'window.lazySizesConfig.loadMode=' . intval($this->options['loadmode']) . ';';
        }
        if ($this->options['preloadafterload']) {
            $lazySizesConfig .= 'window.lazySizesConfig.preloadAfterLoad=true;';
        }

        wp_add_inline_script($this->plugin_name . '_lazysizes', $lazySizesConfig, 'before');
    }
}
