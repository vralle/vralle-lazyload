<?php namespace Vralle\Lazyload\App;

/**
 * The public-facing functionality of the plugin.
 *
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
     *
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
     *
     * @return string A name of CSS-class
     */
    private function getLazyClass()
    {
        return \apply_filters(
            'vralle_lazyload_css_class',
            'lazyload'
        );
    }

    /**
     * Retrive the image, that will be displayed instead of the original
     *
     * @return string A link to image or base64 image
     */
    private function getImgPlaceholder()
    {
        return \apply_filters(
            'vralle_lazyload_image_placeholder',
            'data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='
        );
    }

    private function getEmbedTags()
    {
        return array(
            'iframe',
            'embed',
            'object',
            'video',
        );
    }

    /**
     * Filter the list of attachment image attributes.
     *
     * @param  array $attrs List attributes for the image markup.
     * @return array List of attachment image attributes.
     */
    public function wpGetAttachmentImageAttributes($attrs)
    {
        if (!isset($this->options['wp_images']) || $this->isExit()) {
            return $attrs;
        }

        return $this->attrHandler($attrs, 'img');
    }

    /**
     * Filter the avatar to retrieve.
     *
     * @param  string $html output for the user's avatar.
     * @return string image element markup.
     */
    public function getAvatar($html)
    {
        if (!isset($this->options['avatar']) || $this->isExit()) {
            return $html;
        }

        return $this->contentHandler($html, array('img'));
    }

    /**
     * Filter the post content.
     *
     * @param  string $html Content of the current post.
     * @return string Content of the current post.
     */
    public function theContent($html)
    {
        $tags = array();

        if (isset($this->options['content_images'])) {
            $tags[] = 'img';
        }

        if (isset($this->options['content_embed'])) {
            $tags = \array_merge($tags, $this->getEmbedTags());
        }

        if (empty($tags) || $this->isExit()) {
            return $html;
        }

        return $this->contentHandler($html, $tags);
    }

    /**
     * Search html tags and processing
     *
     * @param  string $html Content.
     * @param  array  $tags List of tag names
     * @return string Content
     */
    private function contentHandler($html, $tags)
    {
        $pattern = $this->getTagRegex($tags);

        return \preg_replace_callback(
            "/$pattern/i",
            function ($m) {
                $tag = $m[1];
                $attrs_in = $m[2];
                /**
                 * @link https://developer.wordpress.org/reference/functions/shortcode_parse_atts/
                 */
                $attrs_in_arr = \shortcode_parse_atts($attrs_in);

                $attrs_out_arr = $this->attrHandler($attrs_in_arr, $tag);

                if ($attrs_out_arr !== $attrs_in_arr) {
                    $attrs_out = '';
                    foreach ($attrs_out_arr as $key => $value) {
                        if (is_int($key)) {
                            $attrs_out .= ' ' . \esc_attr($value);
                        } else {
                            $attrs_out .= \sprintf(' %s="%s"', esc_attr($key), \esc_attr($value));
                        }
                    }
                    $m[0] = \str_replace($attrs_in, $attrs_out, $m[0]);
                }

                return $m[0];
            },
            $html
        );
    }

    /**
     * Image attributes handler
     *
     * @param  array    $attrs   List of image attributes and their values.
     * @param  string   $tag     html tag name
     * @return array    List of image attributes and their values
     */
    private function attrHandler($attrs, $tag)
    {
        $lazy_css_class = $this->getLazyClass();
        $img_placeholder = $this->getImgPlaceholder();
        $css_exception = \array_map('trim', \explode(' ', $this->options['css_exception']));
        $embed_tags = $this->getEmbedTags();
        $is_embed = \in_array($tag, $embed_tags);
        $css_classes = array();
        $have_src = false;

        if (isset($attrs['class'])) {
            $css_classes = \explode(' ', $attrs['class']);

            // Exit if one of the exception classes is present
            if (!empty(\array_intersect($css_exception, $css_classes))) {
                return $attrs;
            }

            // Exit if lazy loading class is present
            if (in_array($lazy_css_class, $css_classes)) {
                return $attrs;
            }
        }

        if (isset($attrs['srcset'])) {
            if (isset($this->options['do_srcset'])) {
                $attrs['data-srcset'] = $attrs['srcset'];
                $attrs['srcset'] = $img_placeholder;
                $have_src = true;

                if (isset($this->options['data-sizes'])) {
                    $attrs['data-sizes'] = 'auto';
                    unset($attrs['sizes']);
                }
            }
        } elseif (isset($attrs['src'])) {
            if (isset($this->options['do_src']) || $is_embed) {
                $attrs['data-src'] = $attrs['src'];
                if ($is_embed) {
                    // set valid src for iframe
                    $attrs['src'] = 'about:blank';
                } else {
                    $attrs['src'] = $img_placeholder;
                }
                // Cleanup Dry Tags
                unset($attrs['sizes']);
                $have_src = true;
            }
        }

        // Do lazyloaded, only if the image have src or srcset
        if ($have_src) {
            $css_classes[] = $lazy_css_class;
            $attrs['class'] = \implode(' ', $css_classes);
            if (0 != $this->options['data-expand']) {
                $attrs['data-expand'] = $this->options['data-expand'];
            }
            if (isset($this->options['parent-fit'])) {
                $attrs['data-parent-fit'] = $this->options['object-fit'];
            }
            // Aspect ratio draft
            if (isset($attrs['width']) && isset($attrs['height'])) {
                $attrs['data-aspectratio'] = $attrs['width'] . '/' . $attrs['height'];
            }
        }

        return $attrs;
    }

    /**
     * Retrieve the html tag regular expression
     *
     * @param  array $tags List of tag names
     * @return string The html tag search regular expression
     */
    private function getTagRegex($tags)
    {
        $tags = \implode('|', array_map('preg_quote', $tags));

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
     * State detection to skip processing
     *
     * @return boolean
     */
    private function isExit()
    {
        // Feed
        if (is_feed()) {
            return true;
        }

        // Preview mode
        if (is_preview()) {
            return true;
        }

        // Print
        if (1 === intval(get_query_var('print'))) {
            return true;
        }

        // Print
        if (1 === intval(get_query_var('printpage'))) {
            return true;
        }

        /**
         * Exit filter
         * @var boolean
         */
        if (!apply_filters('do_vralle_lazyload', true)) {
            return true;
        }

        /**
         * On an AMP version of the posts
         */
        if (defined('AMP_QUERY_VAR') && function_exists('is_amp_endpoint') && is_amp_endpoint()) {
            return true;
        }

        return false;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueueScripts()
    {
        if ($this->isExit()) {
            return;
        }

        $debug_suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';
        $plugin_url = \plugins_url('', \dirname(__FILE__));
        $ID_lazysizes = $this->plugin_name . '_lazysizes';

        \wp_register_script(
            $ID_lazysizes,
            $plugin_url . '/vendor/lazysizes/lazysizes' . $debug_suffix . '.js',
            array(),
            '4.1.8',
            true
        );

        $plugins = $this->getPluginsList();

        if (empty($plugins)) {
            \wp_enqueue_script($this->plugin_name . '_lazysizes');
        } else {
            foreach ($plugins as $plugin) {
                \wp_enqueue_script(
                    $this->plugin_name . '_ls.' . $plugin,
                    $plugin_url . '/vendor/lazysizes/plugins/' . $plugin . '/ls.' . $plugin . $debug_suffix . '.js',
                    array($ID_lazysizes),
                    '4.1.8',
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
        if ($lazySizesConfig !== '') {
            $lazySizesConfig = 'window.lazySizesConfig = window.lazySizesConfig || {};' . $lazySizesConfig;
            \wp_add_inline_script($this->plugin_name . '_lazysizes', $lazySizesConfig, 'before');
        }
    }

    public function addPicturefill()
    {
        if (!isset($this->options['picturefill']) || $this->isExit()) {
            return;
        }

        $src = \plugins_url('vendor/picturefill/dist/picturefill.min.js', \dirname(__FILE__));
        $picturefill_loader = <<<EOT
<script>(function(d,s,id) {
  if ('srcset' in d.createElement('img')) return;
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s);
  js.id = id;
  js.async = true;
  js.src = '$src';
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'picturefill'));</script>
EOT;
        echo $picturefill_loader;
    }

    /**
     * Create a list of lazysize.js plug-ins
     *
     * @return  array List of plugins
     */
    private function getPluginsList()
    {
        // Extensions list from options for possible load
        $list_of_extensions = array(
            'parent-fit',
            'aspectratio',
        );
        $plugins = array();
        foreach ($list_of_extensions as $extension) {
            if (isset($this->options[$extension])) {
                $plugins[] = $extension;
            }
        }
        $plugins = \apply_filters('lazysizes_plugins', $plugins);

        return $plugins;
    }
}
