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
    /**
     * The ID of this plugin.
     * @since    0.1.0
     *
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     * @since    0.1.0
     *
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Marker class for all elements which should be lazy loaded
     * @var string
     */
    private $lazy_class;

    /**
     * The image, that will be displayed instead of the original
     * @var string
     */
    private $img_placeholder;

    /**
     * @var Options
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     *
     * @since   0.1.0
     * @param   string    $plugin_name  The name of the plugin.
     * @param   string    $version      The version of this plugin.
     */
    public function __construct($plugin_name, $version, Options $options)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->options = $options->get();
        $this->getLazyClass();
        $this->getImgPlaceholder();
    }

    /**
     * Create CSS Marker class for all elements which should be lazy loaded
     * @return string
     */
    private function getLazyClass()
    {
        /**
         * @since 0.8.0
         */
        $this->lazy_class = \apply_filters('vralle_lazyload_lazy_class', 'lazyload');
    }

    /**
     * Create The image, that will be displayed instead of the original
     * @since 0.8.0
     */
    private function getImgPlaceholder()
    {
        $this->img_placeholder = \apply_filters('vralle_lazyload_image_placeholder', 'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
    }

    /**
     * Filter the list of attachment image attributes.
     * @link https://developer.wordpress.org/reference/hooks/wp_get_attachment_image_attributes/
     * @since 0.8.0
     *
     * @param   array   $attr_arr   Attributes for the image markup.
     * @return  array   List of attachment image attributes.
     */
    public function wpGetAttachmentImageAttributes($attr_arr)
    {
        if (1 !== intval($this->options['wp_images'])) {
            return $attr_arr;
        }

        if ($this->isExit()) {
            return $attr_arr;
        }

        $new_attr = $this->attrHandler($attr_arr);

        if ($new_attr) {
            $attr_arr = $new_attr;
        }

        return $attr_arr;
    }


    /**
     * Filter the markup of header images.
     * @link https://developer.wordpress.org/reference/hooks/get_header_image_tag/
     * @since 0.8.0
     *
     * @param string    $html   The HTML image tag markup being filtered.
     * @return string   HTML image element markup.
     */
    public function getHeaderImageTag($html)
    {
        if (1 !== intval($this->options['custom_header'])) {
            return $html;
        }

        if ($this->isExit()) {
            return $html;
        }

        $html = $this->contentHandler($html);

        return $html;
    }

    /**
     * Filter the avatar to retrieve.
     * @link https://developer.wordpress.org/reference/hooks/get_avatar/
     * @since 0.8.0
     *
     * @param   string  $html   HTML for the user's avatar.
     * @return  string  HTML image element markup.
     */
    public function getAvatar($html)
    {
        if (1 !== intval($this->options['avatar'])) {
            return $html;
        }

        if ($this->isExit()) {
            return $html;
        }

        $html = $this->contentHandler($html);

        return $html;
    }

    /**
     * Filter the post content.
     * @link https://developer.wordpress.org/reference/hooks/the_content/
     * @since    0.8.0
     *
     * @param   string  $html   Content of the current post.
     * @return  string  Content of the current post.
     */
    public function theContent($html)
    {
        if ($this->isExit()) {
            return $html;
        }

        $tags = array();

        if (1 == intval($this->options['content_images'])) {
            $tags[] = 'img';
        }

        if (1 == intval($this->options['content_iframes'])) {
            $tags[] = 'iframe';
        }

        if (empty($tags)) {
            return $html;
        }

        $html = $this->contentHandler($html, $tags);

        return $html;
    }

    /**
     * State detection to skip processing if necessary
     * @since 0.1.0
     * @return boolean
     */
    private function isExit()
    {
        // Admin menu
        if (\is_admin()) {
            return true;
        }

        // Feed
        if (\is_feed()) {
            return true;
        }

        // Preview mode
        if (\is_preview()) {
            return true;
        }

        // Print
        if (1 === \intval(\get_query_var('print'))) {
            return true;
        }

        if (1 === \intval(\get_query_var('printpage'))) {
            return true;
        }

        /**
         * Exit filter
         * @since 0.8.0
         * @var boolean
         */
        if (!\apply_filters('do_vralle_lazyload', true)) {
            return true;
        }

        /**
         * On an AMP version of the posts
         * @since 0.8.0
         */
        if (\defined('AMP_QUERY_VAR') && \function_exists('is_amp_endpoint') && \is_amp_endpoint()) {
            return true;
        }

        return false;
    }

    /**
     * Looking for html image tags and processing
     * @since 0.8.0
     *
     * @param   string  $html Content.
     * @param   array   $tags List tag names for looking.
     * @return  string  Content.
     */
    private function contentHandler($html = '', $tags = array('img'))
    {

        $util = new Util();
        $pattern = $util->getTagRegex($tags);

        return \preg_replace_callback(
            "/$pattern/i",
            function ($m) {
                $tag = $m[1];
                $attrs = $m[2];
                /**
                 * The wordpress handler is used. This may seem redundant, but it is reliable and verified
                 * @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
                 */
                $parced = \shortcode_parse_atts($attrs);

                $attr_arr = $this->attrHandler($parced, $tag);

                if ($attr_arr) {
                    $attr = '';
                    foreach ($attr_arr as $key => $value) {
                        // xss test ok
                        if (is_int($key)) {
                            $attr .= ' ' . esc_attr($value);
                        } else {
                            $attr .= \sprintf(' %s="%s"', $key, esc_attr($value));
                        }
                    }
                    $m[0] = \str_replace($attrs, $attr, $m[0]);
                }

                return $m[0];
            },
            $html
        );
    }

    /**
     * Image attribute handler
     * @since    0.8.0
     *
     * @param  array    $attr_arr   List of image attributes and their values.
     * @param  string   $tag        current tag
     *
     * @return mixed    Array List of image attributes and their values,
     *                  where the necessary attributes for the loader are added
     *                  or false, if exclude
     */
    private function attrHandler($attr_arr = array(), $tag = 'img')
    {
        $lazy_class = $this->lazy_class;
        $img_placeholder = $this->img_placeholder;
        $classes_arr = array();
        $have_src = false;
        $exlude_class_arr = \array_map('trim', \explode(' ', $this->options['exclude_class']));

        // Exit by CSS class
        if (isset($attr_arr['class'])) {
            $classes_arr = \explode(' ', $attr_arr['class']);

            if (!empty(\array_intersect($exlude_class_arr, $classes_arr))) {
                return false;
            }

            if (false !== \array_search($lazy_class, $classes_arr)) {
                return false;
            }
        }

        if (isset($attr_arr['srcset'])) {
            if (1 === intval($this->options['do_srcset'])) {
                $attr_arr['data-srcset'] = $attr_arr['srcset'];
                $attr_arr['srcset'] = $img_placeholder;
                $have_src = true;

                if (1 === intval($this->options['data-sizes'])) {
                    $attr_arr['data-sizes'] = 'auto';
                    unset($attr_arr['sizes']);
                }
            }
        } elseif (isset($attr_arr['src'])) {
            if (1 === intval($this->options['do_src']) || 'iframe' === $tag) {
                // xss test ok
                $attr_arr['data-src'] = esc_url($attr_arr['src']);
                if ('iframe' === $tag) {
                    $attr_arr['src'] = '';
                } else {
                    $attr_arr['src'] = $img_placeholder;
                }
                $have_src = true;
            }
        }

        // Do lazyloaded, only if the image have src or srcset
        if ($have_src) {
            $classes_arr[] = $lazy_class;
            $attr_arr['class'] = \implode(' ', $classes_arr);
            if ($this->options['data-expand']) {
                $attr_arr['data-expand'] = $this->options['data-expand'];
            }
        }

        return $attr_arr;
    }

    /**
     * Create a list of lazysize.js plug-ins
     * @since   0.8.0
     *
     * @return  mixed array List of plugins or false, if empty
     */
    private function getPluginsList()
    {
        $plugins = \apply_filters('lazysizes_plugins', array());

        if (!is_array($plugins) || empty($plugins)) {
            return false;
        }

        return $plugins;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     * @since    0.8.0
     */
    public function enqueueScripts()
    {
        $debug_suffix = (\defined('SCRIPT_DEBUG') && \SCRIPT_DEBUG) ? '' : '.min';
        $lazysizes_dir_url = \trailingslashit(\plugin_dir_url(\dirname(__FILE__)) . 'vendor/lazysizes');

        \wp_register_script(
            $this->plugin_name . '_lazysizes',
            $lazysizes_dir_url . 'lazysizes' . $debug_suffix . '.js',
            array(),
            $this->version,
            true
        );

        $plugins = $this->getPluginsList();

        if (!$plugins) {
            \wp_enqueue_script($this->plugin_name . '_lazysizes');
        } else {
            foreach ($plugins as $plugin) {
                \wp_enqueue_script(
                    $this->plugin_name . '_ls.' . $plugin,
                    $lazysizes_dir_url . 'plugins/' . $plugin . '/ls.' . $plugin . $debug_suffix . '.js',
                    array($this->plugin_name . '_lazysizes'),
                    $this->version,
                    true
                );
            }
        }

        $lazySizesConfig = '';
        // Skip default value
        if (2 !== intval($this->options['loadmode'])) {
            $lazySizesConfig .= 'window.lazySizesConfig.loadMode=' . \intval($this->options['loadmode']) . ';';
        }

        if (1 === intval($this->options['preloadafterload'])) {
            $lazySizesConfig .= 'window.lazySizesConfig.preloadAfterLoad=true;';
        }

        // Config if only need
        if (!empty($lazySizesConfig)) {
            $lazySizesConfig = 'window.lazySizesConfig = window.lazySizesConfig || {};' . $lazySizesConfig;
            \wp_add_inline_script($this->plugin_name . '_lazysizes', $lazySizesConfig, 'before');
        }
    }
}
