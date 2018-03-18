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
        $settings_url  = menu_page_url($this->plugin_name, false);
        $settings_link = "<a href='$settings_url'>" . esc_html__('Settings', 'vralle-lazyload') . "</a>";
        array_unshift($links, $settings_link);

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
                'title' => 'Выбор изображений для ленивой загрузки',
            ),
            array(
                'id' => 'responsive',
                'title' => \__('Responsive images', 'vralle-lazyload'),
            ),
            array(
                'id' => 'exclude',
                'title' => \__('Exclude', 'vralle-lazyload'),
            ),
            array(
                'id' => 'fine_tuning',
                'title' => \__('Fine tuning', 'vralle-lazyload'),
            )
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

        $value = $options[$arguments['uid']];

        $output = '';

        switch ($arguments['type']) {
            case 'text':
                $output .= \sprintf(
                    '<input name="%1$s[%2$s]" id="%1$s[%2$s]" class="%3$s" type="%4$s" placeholder="%5$s" value="%6$s" />',
                    $this->plugin_name,
                    $arguments['uid'],
                    isset($arguments['class']) ? \esc_attr($arguments['class']) : '',
                    $arguments['type'],
                    isset($arguments['placeholder']) ? \esc_attr($arguments['placeholder']) : '',
                    $value
                );
                break;
            case 'number':
                if (!isset($arguments['step'])) {
                    $arguments['step'] = 'any';
                }

                $output .= \sprintf(
                    '<input name="%1$s[%2$s]" id="%1$s[%2$s]" type="number" placeholder="%3$s" min="%4$s" max="%5$s" step="%6$s" value="%7$s" />',
                    $this->plugin_name,
                    $arguments['uid'],
                    isset($arguments['placeholder']) ? \esc_attr($arguments['placeholder']) : '',
                    isset($arguments['min']) ? \esc_attr($arguments['min']) : '',
                    isset($arguments['max']) ? \esc_attr($arguments['max']) : '',
                    \esc_attr($arguments['step']),
                    $value
                );
                break;
            case 'checkbox':
                $output .= \sprintf(
                    '<input type="checkbox" id="%1$s[%2$s]" name="%1$s[%2$s]" value="1" %3$s>',
                    $this->plugin_name,
                    $arguments['uid'],
                    \checked('1', $value, false)
                );

                break;
        }

        if (isset($arguments['label'])) {
            $output = \sprintf(
                '<label for="%1$s[%2$s]">%3$s %4$s</label>',
                $this->plugin_name,
                $arguments['uid'],
                $output,
                $arguments['label']
            );
        }

        if (isset($arguments['description'])) {
            $output .= \sprintf(
                '<p class="description">%s</p>',
                $arguments['description']
            );
        }

        if (isset($arguments['label']) || isset($arguments['description'])) {
            $output = \sprintf(
                '<fieldset><legend class="screen-reader-text"><span>%s</span></legend>%s</fieldset>',
                $arguments['title'],
                $output
            );
        }

        echo $output;
    }
}
