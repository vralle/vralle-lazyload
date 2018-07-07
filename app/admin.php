<?php

namespace Vralle\Lazyload\App;

/**
 * Creating a custom settings page of the plugin.
 * @since 0.8.0
 */
class Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    0.8.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The options of this plugin
     *
     * @since    0.8.0
     * @access   private
     * @var      Options    $options    .
     */
    private $options;

    public function __construct($plugin_name, $options)
    {
        $this->plugin_name = $plugin_name;
        $this->options = $options;
    }

    /**
     * Add the menu item and page
     * @since 0.8.0
     */
    public function addAdminPage()
    {
        $page_title = 'VRALLE.Lazyload';
        $menu_title = $page_title;
        $capability = 'manage_options';
        $slug = $this->plugin_name;
        $callback = array( $this, 'renderAdminPage' );

        \add_options_page(
            // The text to be displayed in the title tags of the page when the menu is selected.
            $page_title,
            // The text to be used for the menu.
            $menu_title,
            // Which type of users can see this menu item
            $capability,
            // The unique ID, menu_slug
            $slug,
            // callback function
            $callback
        );
    }

    /**
     * Add Settings Link to plugins page
     * @since 0.8.0
     *
     * @param $links
     * @return array
     */
    public function addSettingsLink($links)
    {
        $settings_url  = \menu_page_url($this->plugin_name, false);
        $settings_link = "<a href='$settings_url'>" . \esc_html__('Settings', 'vralle-lazyload') . "</a>";
        \array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Render the plugin settings page
     * @since 0.8.0
     */
    public function renderAdminPage()
    {
        include_once(dirname(__FILE__) . '/views/admin-page.php');
    }

    /**
     * Register the plugin setting
     * @since 0.8.0
     */
    public function registerSetting()
    {
        /**
         * Register a setting and its data.
         * @param string $option_group A settings group name. Should correspond to a whitelisted option key name.
         *  Default whitelisted option key names include "general," "discussion," and "reading," among others.
         * @param string $option_name The name of an option to sanitize and save.
         * @param array  $args {
         *     Data used to describe the setting when registered.
         *
         *     @type string   $type              The type of data associated with this setting.
         *                                       Valid values are 'string', 'boolean', 'integer', and 'number'.
         *     @type string   $description       A description of the data attached to this setting.
         *     @type callable $sanitize_callback A callback function that sanitizes the option's value.
         *     @type bool     $show_in_rest      Whether data associated with this setting should be included in the REST API.
         *     @type mixed    $default           Default value when calling `get_option()`.
         * }
         */
        \register_setting(
            // group name
            $this->plugin_name,
            // option name
            $this->plugin_name,
            array()
        );
    }

    /**
     * Add the sections to the plugin settings page.
     * @since 0.8.0
     */
    public function addSettingSections()
    {
        $sections = array(
            array(
                'id' => 'images',
                'title' => \esc_html__('Lazy loading', 'vralle-lazyload'),
            ),
            array(
                'id' => 'responsive',
                'title' => \esc_html__('Responsive images', 'vralle-lazyload'),
            ),
            array(
                'id' => 'exclude',
                'title' => \esc_html__('Exclude', 'vralle-lazyload'),
            ),
            array(
                'id' => 'fine_tuning',
                'title' => \esc_html__('Fine tuning', 'vralle-lazyload'),
            ),
            array(
                'id' => 'extensions',
                'title' => \esc_html__('Extensions', 'vralle-lazyload'),
            ),
        );

        foreach ($sections as $section) {
            \add_settings_section(
                // Section ID
                $section['id'],
                // Section Title
                $section['title'],
                // Callback for Subheader
                array( $this, 'sectionCallback' ),
                // Page ID
                $this->plugin_name
            );
        }
    }

    /**
     * Setting Section collback
     * @since 0.8.0
     *
     * @param   array   $arguments list of the section options
     */
    public function sectionCallback($arguments)
    {
    }

    /**
     * Add new fields to sections of the plugin settings page
     * @since 0.8.0
     */
    public function addSettingFields()
    {
        $options = $this->options;
        $settings = $options->getSettings();

        foreach ($settings as $field) {
            \add_settings_field(
                // Field ID
                $field['uid'],
                // Field Title
                $field['title'],
                // Callback for render Input
                array( $this, 'fieldCallback' ),
                // Page ID
                $this->plugin_name,
                // Section ID
                $field['section'],
                // Args for callback
                $field
            );
        }
    }

    /**
     * Render a field of the plugin settings page
     * @since 0.8.0
     * @param  array $arguments list of the plugin options
     */
    public function fieldCallback($arguments)
    {
        $options = $this->options->get();

        $output = '';

        switch ($arguments['type']) {
            case 'text':
                $output .= \sprintf(
                    '<input name="%1$s[%2$s]" id="%1$s[%2$s]" class="%3$s" type="%4$s" placeholder="%5$s" value="%6$s" maxlength="120" />',
                    \esc_attr($this->plugin_name),
                    \esc_attr($arguments['uid']),
                    isset($arguments['class']) ? \esc_attr($arguments['class']) : '',
                    \esc_attr($arguments['type']),
                    isset($arguments['placeholder']) ? \esc_attr($arguments['placeholder']) : '',
                    esc_attr($options[$arguments['uid']])
                );
                break;
            case 'number':
                if (!isset($arguments['step'])) {
                    $arguments['step'] = 'any';
                }

                $output .= \sprintf(
                    '<input name="%1$s[%2$s]" id="%1$s[%2$s]" type="number" placeholder="%3$s"%4$s%5$s step="%6$s" value="%7$s" maxlength="12" />',
                    \esc_attr($this->plugin_name),
                    \esc_attr($arguments['uid']),
                    isset($arguments['placeholder']) ? \esc_attr($arguments['placeholder']) : '',
                    isset($arguments['min']) ? ' min="' . \esc_attr($arguments['min']) . '"' : '',
                    isset($arguments['max']) ? ' max="' . \esc_attr($arguments['max']) . '"': '',
                    \esc_attr($arguments['step']),
                    esc_attr($options[$arguments['uid']])
                );
                break;
            case 'checkbox':
                $disable = '';
                if ('parent-fit' == $arguments['uid']) {
                    $disable = \disabled('', isset($options['data-sizes']), false);
                }
                $output .= \sprintf(
                    '<input type="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s%4$s>',
                    \esc_attr($this->plugin_name),
                    \esc_attr($arguments['uid']),
                    \checked(isset($options[$arguments['uid']]), true, false),
                    $disable
                );

                break;

            case 'select':
                $disable = '';
                if ('object-fit' == $arguments['uid']) {
                    if (!isset($options['data-sizes']) || !isset($options['parent-fit'])) {
                        $disable = ' disabled="disabled"';
                    }
                }
                $output .= \sprintf(
                    '<select name="%1$s[%2$s]" id="%1$s[%2$s]"%3$s>',
                    \esc_attr($this->plugin_name),
                    \esc_attr($arguments['uid']),
                    $disable
                );
                foreach ($arguments['options'] as $key => $text) {
                    $output .= \sprintf(
                        '<option value="%s"%s>%s</option>',
                        \esc_attr($key),
                        \selected($key, $options[$arguments['uid']], false),
                        \esc_attr($text)
                    );
                }
                $output .= '</select>';

                break;
        }

        if (isset($arguments['label'])) {
            if ('select' == $arguments['type']) {
                $output .= \sprintf(
                    ' <label for="%1$s[%2$s]">%3$s</label>',
                    \esc_attr($this->plugin_name),
                    \esc_attr($arguments['uid']),
                    \esc_attr($arguments['label'])
                );
            } else {
                $output = \sprintf(
                    '<label for="%1$s[%2$s]">%3$s %4$s</label>',
                    \esc_attr($this->plugin_name),
                    \esc_attr($arguments['uid']),
                    $output,
                    \esc_attr($arguments['label'])
                );
            }
        }

        if (isset($arguments['description'])) {
            $output .= \sprintf(
                '<p class="description">%s</p>',
                \wp_kses($arguments['description'], 'default')
            );
        }

        if (isset($arguments['label']) || isset($arguments['description'])) {
            $output = \sprintf(
                '<fieldset><legend class="screen-reader-text"><span>%s</span></legend>%s</fieldset>',
                esc_attr($arguments['title']),
                $output
            );
        }

        echo $output;
    }
}
