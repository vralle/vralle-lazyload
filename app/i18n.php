<?php
namespace Vralle\Lazyload\App;

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://github.com/vralle/VRALLE.Lazyload
 * @since      0.1.0
 * @package    Vralle_Lazyload
 * @subpackage Vralle_Lazyload/app
 */
class i18n
{
    /**
     * Load the plugin text domain for translation.
     *
     * @since    0.1.0
     */
    public function load_plugin_textdomain()
    {
        \load_plugin_textdomain(
            'vralle-lazyload',
            false,
            \dirname(\dirname(\plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
