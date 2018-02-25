<?php
namespace Vralle\Lazyload\App;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.1.0
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 */
class Settings
{
    const PLUGIN_OPTION = array(
        'admin_page' => 'vralle-lazyload',
        'id' => 'vralle_lazyload',
        'group' => 'vralle_lazyload_wp_images',
        'section' => 'wp_images_section',
        'section_dev' => 'dev_section',
        'default' => array(
            'wp_images' => '1',
            'content_images' => '1',
            'avatar' => '1',
            'do_src' => '1',
            'do_srcset' => '1',
            'data-sizes' => '1',
            'exclude_class' => '',
            'bgset' => '0',
            'unveilhooks' => '0',
        )
    );

    const LS_PLUGINS = array(
        'bgset' => array(),
        'unveilhooks' => array()
    );

    private $options;

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
     * Initialize the class and set its properties.
     *
     * @since    0.1.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Add a top-level menu page.
     */
    public function register_settings_page()
    {
        \add_menu_page(
            // The text to be displayed in the title tags of the page when the menu is selected.
            'VRALLE.Lazyload',
            // The text to be used for the menu.
            'VRALLE.Lazyload',
            // Which type of users can see this menu item
            'manage_options',
            // The unique ID, menu_slug
            self::PLUGIN_OPTION['admin_page'],
            // callback function
            array($this, 'create_admin_page'),
            // The URL to the icon / a base64-encoded SVG using a data URI / the name of a Dashicons / none
            'dashicons-format-gallery',
            // position
            11
        );
    }

    /**
     * Renders a simple page to display for the theme menu defined above.
     */
    public function create_admin_page()
    {
        $this->options = \get_option(self::PLUGIN_OPTION['id']);
        include_once(dirname(__FILE__) . '/views/settings_page.php');
    }

    public function define_fields_config()
    {
        /**
         * @link https://developer.wordpress.org/reference/functions/register_setting/
         */
        \register_setting(
            self::PLUGIN_OPTION['group'], // option_group
            self::PLUGIN_OPTION['id'], // option_name
            // Args
            array(
                // The type of data associated with this setting. Valid values are
                // 'string', 'boolean', 'integer', and 'number'.
                'type' => 'string',
                // A description of the data attached to this setting.
                'description' => \esc_html__('Settings Description', 'vralle-lazyload'),
                // A callback function that sanitizes the option's value.
                'sanitize_callback' => array($this, 'sanitize_options'),
                // Default value when calling get_option().
                'default' => self::PLUGIN_OPTION['default']
            )
        );

        /**
         * @link https://developer.wordpress.org/reference/functions/add_settings_section/
         */
        \add_settings_section(
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Section Title
            \esc_html__('Lazy loading of images', 'vralle-lazyload'),
            // Callback for Subheader
            function () {
                \printf(
                    '<p>%s</p>',
                    \esc_html__('Section Description', 'vralle-lazyload')
                );
            },
            // Page ID
            self::PLUGIN_OPTION['admin_page']
        );

        /**
         * Add Settings Fields
         * @link https://developer.wordpress.org/reference/functions/add_settings_field/
         */

        // Wordpress Images
        \add_settings_field(
            // Field ID
            'wp_images',
            // Field Title
            \esc_html__(
                'Apply to Wordpress images',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            // Args for callback
            array(
                'id' => 'wp_images',
                'title' => \esc_html__(
                    'Apply to Wordpress images',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'Uses lazy loading of images, which are displayed through the WordPress engine. For example, the Post Thumbnails or Featured Image.',
                    'vralle-lazyload'
                ),
            )
        );

        // The content images
        \add_settings_field(
            // Field ID
            'content_images',
            // Field Title
            \esc_html__(
                'Apply to content images',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            // Args for callback
            array(
                'id' => 'content_images',
                'title' => \esc_html__(
                    'Lazy loading the Wordpress images',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'If the plugin finds the image code in the content, lazy loading will be added to the output.',
                    'vralle-lazyload'
                )
            )
        );

        // Lazy load The Avatar
        \add_settings_field(
            // Field ID
            'avatar',
            // Field Title
            \esc_html__(
                'Apply to Avatar',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            // Args for callback
            array(
                'id' => 'avatar',
                'title' => \esc_html__(
                    'Apply to Avatar',
                    'vralle-lazyload'
                ),
            )
        );

        // Non-responsive images
        \add_settings_field(
            // Field ID
            'do_src',
            // Field Title
            \esc_html__(
                'Lazy loading the non-responsive images',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            array(
                'id' => 'do_src',
                'title' => \esc_html__(
                    'Lazy loading the non-responsive images',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'Non-responsive images do not have the attribute srcset',
                    'vralle-lazyload'
                ),
            )
        );

        // Responsive images
        \add_settings_field(
            // Field ID
            'do_srcset',
            // Field Title
            \esc_html__(
                'Lazy loading the responsive images',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            array(
                'id' => 'do_srcset',
                'title' => \esc_html__(
                    'Lazy loading the responsive images',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'responsive images have the srcset attribute',
                    'vralle-lazyload'
                ),
            )
        );

        // Calculate the sizes
        \add_settings_field(
            // Field ID
            'data-sizes',
            // Field Title
            \esc_html__(
                'Calculate the sizes using the lazysizes',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            array(
                'id' => 'data-sizes',
                'title' => \esc_html__(
                    'Calculate the sizes using the lazysizes',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'This will replace the values of the "sizes" attribute specified by Wordpress',
                    'vralle-lazyload'
                )
            )
        );

        // Exlude class
        \add_settings_field(
            // Field ID
            'exclude_class',
            // Field Title
            \esc_html__(
                'Exclude Images by CSS Class',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_text_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section'],
            // Args for callback
            array(
                'id' => 'exclude_class',
                'title' => \esc_html__(
                    'Exclude Images by CSS Class',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'CSS Classes of images, that need to be excluded from lazy loading. Space separated',
                    'vralle-lazyload'
                ),
            )
        );


        /**
         * @link https://developer.wordpress.org/reference/functions/add_settings_section/
         */
        \add_settings_section(
            // Section ID
            self::PLUGIN_OPTION['section_dev'],
            // Section Title
            \esc_html__('Developer settings', 'vralle-lazyload'),
            // Callback for Subheader
            function () {
                \printf(
                    '<p>%s</p>',
                    \esc_html__('The settings below require editing the template code. Use these settings only if you understand what you are doing. By default, these settings are not used.', 'vralle-lazyload')
                );
            },
            // Page ID
            self::PLUGIN_OPTION['admin_page']
        );

        /**
         * Add Settings Fields
         * @link https://developer.wordpress.org/reference/functions/add_settings_field/
         */

        \add_settings_field(
            // Field ID
            'bgset',
            // Field Title
            \esc_html__(
                'lazysizes bgset extension - responsive background images',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section_dev'],
            // Args for callback
            // Args for callback
            array(
                'id' => 'bgset',
                'title' => \esc_html__(
                    'lazysizes bgset extension - responsive background images',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'This simple and small plugin allows you to define multiple background images with a width descriptor, similar to how img[srcset] works as also art directed images using media queries, similar to how picture works.',
                    'vralle-lazyload'
                )
            )
        );

        // Default Image Sizes
        \add_settings_field(
            // Field ID
            'unveilhooks',
            // Field Title
            \esc_html__(
                'lazysizes unveilhooks extension',
                'vralle-lazyload'
            ),
            // Callback for render Input
            array($this, 'input_checkbox_callback'),
            // Page ID
            self::PLUGIN_OPTION['admin_page'],
            // Section ID
            self::PLUGIN_OPTION['section_dev'],
            // Args for callback
            // Args for callback
            array(
                'id' => 'unveilhooks',
                'title' => \esc_html__(
                    'lazysizes unveilhooks extension',
                    'vralle-lazyload'
                ),
                'description' => \esc_html__(
                    'The unveilhooks plugin extends lazySizes to also unveil / lazyload scripts/widgets, background images, styles and video/audio elements',
                    'vralle-lazyload'
                )
            )
        );
    }

    public function input_checkbox_callback($args)
    {
        $output = '<fieldset>';
        if (isset($args['title'])) {
            $output .= \sprintf(
                '<legend class="screen-reader-text"><span>%s</span></legend>',
                $args['title']
            );
        }


        $output .= sprintf(
            '<label for="%1$s"><input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s> %4$s</label>',
            $args['id'],
            self::PLUGIN_OPTION['id'],
            \checked(1, isset($this->options[$args['id']]) ? \absint($this->options[$args['id']]) : 0, false),
            (isset($args['label_text'])) ? $args['label_text'] : ''
        );

        if (isset($args['description'])) {
            $output .= \sprintf(
                '<p class="description">%s</p>',
                $args['description']
            );
        }

        $output .= '</fieldset>';

        echo $output;
    }

    public function input_text_callback($args)
    {
        $output = \sprintf(
            // Render the output
            '<input class="regular-text" type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" />',
            $args['id'],
            self::PLUGIN_OPTION['id'],
            (isset($this->options[$args['id']])) ? $this->options[$args['id']] : ''
        );
        if (isset($args['description'])) {
            $output .= sprintf(
                '<p class="description">%s</p>',
                $args['description']
            );
        }

        echo $output;
    }

    public function sanitize_options($input_values)
    {
        $input_values = (array)$input_values;
        $default = self::PLUGIN_OPTION['default'];
        $is_valid = array();

        foreach ($default as $key => $default) {
            if (\array_key_exists($key, $input_values)) {
                if (\gettype($input_values[$key]) !== \gettype($default)) {
                    $this->add_error($key, 'Data type error. The type of data sent by the form in the ' . $key . ' differs from the type declared in the default values');
                }
                $is_valid[$key] = $input_values[$key];
            } else {
                $is_valid[$key] = $default;
            }
        }

        return $is_valid;
        // return array_map('absint', $is_valid);
    }

    /**
     * Use this to show messages to users about settings validation problems, missing settings or anything else.
     * @param [type] $key     Slug-name to identify the error. Used as part of 'id' attribute in HTML output.
     * @param [type] $message The formatted message text to display (will be shown inside styled <div> and <p> tags).
     */
    private function add_error($key, $message)
    {
        /**
         * Register a settings error to be displayed
         *
         * @link https://developer.wordpress.org/reference/functions/add_settings_error/
         */
        \add_settings_error(
            // Slug title of the setting
            self::PLUGIN_OPTION['id'],
            $key,
            $message,
            // Message type, controls HTML class. Accepts 'error' or 'updated'
            'error'
        );
    }
}
