<?php
namespace Vralle\Lazyload\App;

/**
 * Define the internationalization functionality
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app
 */
class i18n
{
    /**
     * The name of a plugin bootstrap file
     * @var string
     */
    private $plugin_basename;

    public function __construct($plugin_basename)
    {
        $this->plugin_basename = $plugin_basename;
    }

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain()
    {
        \load_plugin_textdomain('vralle-lazyload', false, $this->plugin_basename);
    }
}
