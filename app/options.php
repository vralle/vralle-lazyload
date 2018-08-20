<?php
namespace Vralle\Lazyload\App;

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.7.4
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 */
class Options
{
    protected $name;

    protected $plugin_settings = array();

    protected $current = array();

    /**
     * Initialize the class and set its properties.
     *
     * @since    0.1.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $plugin_settings)
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_settings = $plugin_settings;
    }

    public function getSettings()
    {
        return $this->plugin_settings;
    }

    public function getDefault()
    {
        $default = array();
        foreach ($this->plugin_settings as $option) {
            $default[$option['uid']] = $option['default'];
        }
        return $default;
    }

    public function setupOption()
    {
        $default = $this->getDefault();
        $current = \get_option($this->plugin_name, $default);
        // Where default values are needed
        $support = array(
            'exclude_class',
            'data-expand',
            'loadmode',
            'object-fit',
        );
        $option = array();
        foreach ($default as $key => $value) {
            if (isset($current[$key])) {
                $option[$key] = $current[$key];
            } elseif (in_array($key, $support)) {
                // Set default value
                $option[$key] = $value;
            }
        }

        return $option;
    }

    public function get()
    {
        if (empty($this->current)) {
            $this->current = $this->setupOption();
        }
        return $this->current;
    }
}
