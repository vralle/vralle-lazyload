<?php namespace Vralle\Lazyload\App;

/**
 * The public-facing functionality of the plugin.
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app
 */
class Lazysizes
{
    /**
     * The ID of this plugin.
     * @var string
     */
    private $plugin_name;

    /**
     * The options of this plugin
     * @var array
     */
    private $options;

    /**
     * Initialize the class and set its properties.
     * @param string $plugin_name The name of the plugin.
     * @param string $version     The version of the plugin.
     * @param object $options     The options of the plugin
     */
    public function __construct($plugin_name, $options)
    {
        $this->plugin_name = $plugin_name;
        $this->options = $options;
    }

    /**
     * Retrive the name of CSS-class for all elements which should be lazy loaded
     * @return string A name of CSS-class
     */
    private function getLazyClass()
    {
        return \apply_filters(
            'vralle_lazyload_lazy_class',
            'lazyload'
        );
    }

    /**
     * Retrive the image, that will be displayed instead of the original
     * @return string A link to image
     */
    private function getImgPlaceholder()
    {
        return \apply_filters(
            'vralle_lazyload_image_placeholder',
            'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='
        );
    }

    /**
     * Filter the list of attachment image attributes.
     * @param  array $attr_arr list attributes for the image markup.
     * @return array List of attachment image attributes.
     */
    public function wpGetAttachmentImageAttributes($attr_arr)
    {
        if (!isset($this->options['wp_images'])) {
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
     * Filter the avatar to retrieve.
     * @param  string $html output for the user's avatar.
     * @return string image element markup.
     */
    public function getAvatar($html)
    {
        if (!isset($this->options['avatar'])) {
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
     * @param  string $html Content of the current post.
     * @return string Content of the current post.
     */
    public function theContent($html)
    {
        if ($this->isExit()) {
            return $html;
        }

        $tags = array();

        if (isset($this->options['content_images'])) {
            $tags[] = 'img';
        }

        if (isset($this->options['content_embed'])) {
            $tags[] = 'iframe';
            $tags[] = 'embed';
            $tags[] = 'object';
            $tags[] = 'video';
        }

        if (empty($tags)) {
            return $html;
        }

        $html = $this->contentHandler($html, $tags);

        return $html;
    }

    /**
     * Looking for html tags and processing
     * @param  string $html Content.
     * @param  array  $tags List tag names for looking.
     * @return string Content
     */
    private function contentHandler($html = '', $tags = array('img'))
    {

        $pattern = $this->getTagRegex($tags);

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
                        if (is_int($key)) {
                            $attr .= ' ' . \esc_attr($value);
                        } else {
                            $attr .= \sprintf(' %s="%s"', $key, \esc_attr($value));
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
     * @param  array    $attr_arr   List of image attributes and their values.
     * @param  string   $tag        current tag
     * @return mixed    Array List of image attributes and their values,
     *                  where the necessary attributes for the loader are added
     *                  or false, if exclude
     */
    private function attrHandler($attr_arr = array(), $tag = 'img')
    {
        $lazy_class = $this->getLazyClass();
        $img_placeholder = $this->getImgPlaceholder();
        $classes_arr = array();
        $have_src = false;
        $exlude_class_arr = \array_map('trim', \explode(' ', $this->options['exclude_class']));

        // Exit by CSS class
        if (isset($attr_arr['class'])) {
            $classes_arr = \explode(' ', $attr_arr['class']);

            if (!empty(\array_intersect($exlude_class_arr, $classes_arr))) {
                return false;
            }

            if (in_array($lazy_class, $classes_arr)) {
                return false;
            }
        }

        if (isset($attr_arr['srcset'])) {
            if (isset($this->options['do_srcset'])) {
                $attr_arr['data-srcset'] = $attr_arr['srcset'];
                $attr_arr['srcset'] = $img_placeholder;
                $have_src = true;

                if (isset($this->options['data-sizes'])) {
                    $attr_arr['data-sizes'] = 'auto';
                    unset($attr_arr['sizes']);
                }
            }
        } elseif (isset($attr_arr['src'])) {
            if (isset($this->options['do_src']) || 'iframe' === $tag) {
                $attr_arr['data-src'] = $attr_arr['src'];
                if ('iframe' === $tag) {
                    // set valid src for iframe
                    $attr_arr['src'] = 'about:blank';
                } else {
                    $attr_arr['src'] = $img_placeholder;
                }
                // Cleanup Dry Tags
                unset($attr_arr['sizes']);
                $have_src = true;
            }
        }

        // Do lazyloaded, only if the image have src or srcset
        if ($have_src) {
            $classes_arr[] = $lazy_class;
            $attr_arr['class'] = \implode(' ', $classes_arr);
            if (0 != $this->options['data-expand']) {
                $attr_arr['data-expand'] = $this->options['data-expand'];
            }
            if (isset($this->options['parent-fit'])) {
                $attr_arr['data-parent-fit'] = $this->options['object-fit'];
            }
        }

        return $attr_arr;
    }

    /**
     * Retrieve the html tag regular expression. Overweight, but makes the search bullet-proof.
     * @param  string $tagnames Optional.
     * @return string The html tag search regular expression
     */
    private function getTagRegex($tags = null)
    {
        $tags = \join('|', array_map('preg_quote', $tags));

        return
        '<\s*'                              // Opening tag
        . "($tags)"                         // Tag name
        . '('
        .     '[^>\\/]*'                    // Not a closing tag or forward slash
        .     '(?:'
        .         '\\/(?!>)'                // A forward slash not followed by a closing tag
        .         '[^>\\/]*'                // Not a closing tag or forward slash
        .     ')*?'
        . ')'
        . '\\/?>';                          // Self closing tag ...
    }

    /**
     * State detection to skip processing if necessary
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

        // Print
        if (1 === \intval(\get_query_var('printpage'))) {
            return true;
        }

        /**
         * Exit filter
         * @var boolean
         */
        if (!\apply_filters('do_vralle_lazyload', true)) {
            return true;
        }

        /**
         * On an AMP version of the posts
         */
        if (\defined('AMP_QUERY_VAR') && \function_exists('is_amp_endpoint') && \is_amp_endpoint()) {
            return true;
        }

        return false;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueueScripts()
    {
        $debug_suffix = (\defined('SCRIPT_DEBUG') && \SCRIPT_DEBUG) ? '' : '.min';
        $plugin_url = \plugins_url('', \dirname(__FILE__));
        $ID_lazysizes = $this->plugin_name . '_lazysizes';

        \wp_register_script(
            $ID_lazysizes,
            $plugin_url . '/vendor/lazysizes/lazysizes' . $debug_suffix . '.js',
            array(),
            '4.1.5',
            true
        );

        $plugins = $this->getPluginsList();

        if (!$plugins) {
            \wp_enqueue_script($this->plugin_name . '_lazysizes');
        } else {
            foreach ($plugins as $plugin) {
                \wp_enqueue_script(
                    $this->plugin_name . '_ls.' . $plugin,
                    $plugin_url . '/vendor/lazysizes/plugins/' . $plugin . '/ls.' . $plugin . $debug_suffix . '.js',
                    array($ID_lazysizes),
                    '4.1.5',
                    true
                );
            }
        }

        $lazySizesConfig = '';
        // Skip default value
        if (2 !== intval($this->options['loadmode'])) {
            $lazySizesConfig .= 'window.lazySizesConfig.loadMode=' . \intval($this->options['loadmode']) . ';';
        }

        if (isset($this->options['preloadafterload'])) {
            $lazySizesConfig .= 'window.lazySizesConfig.preloadAfterLoad=true;';
        }

        // Config if only need
        if (!empty($lazySizesConfig)) {
            $lazySizesConfig = 'window.lazySizesConfig = window.lazySizesConfig || {};' . $lazySizesConfig;
            \wp_add_inline_script($this->plugin_name . '_lazysizes', $lazySizesConfig, 'before');
        }
    }

    public function addPicturefill()
    {
        if (!isset($this->options['picturefill'])) {
            return;
        }
        $src = \plugins_url('vendor/picturefill/dist/picturefill.min.js', \dirname(__FILE__));
        echo <<<EOT
<script>(function(d,s,id) {
  if ('srcset' in d.createElement('img')) return;
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s);
  js.id = id;
  js.async = true;
  js.src = "$src";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'picturefill'));</script>
EOT;
    }

    /**
     * Create a list of lazysize.js plug-ins
     * @return  mixed array List of plugins or false, if empty
     */
    private function getPluginsList()
    {
        // Extensions list from options for possible load
        $list_of_extensions = array(
            'parent-fit',
        );
        $plugins = array();
        foreach ($list_of_extensions as $extension) {
            if (isset($this->options[$extension])) {
                $plugins[] = $extension;
            }
        }
        $plugins = \apply_filters('lazysizes_plugins', $plugins);
        if (!is_array($plugins) || empty($plugins)) {
            return false;
        }

        return $plugins;
    }
}
