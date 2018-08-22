<?php
use Vralle\Lazyload\App\Plugin;

/**
 * The plugin bootstrap file
 * @link              https://github.com/vralle/vralle-lazyload
 * @package           vralle-lazyload
 *
 * @wordpress-plugin
 * Plugin Name:       vralle.lazyload
 * Plugin URI:        https://github.com/vralle/vralle-lazyload
 * Description:       Brings lazySizes.js to WordPress
 * Version:           0.9.0
 * Author:            V.Ralle
 * Author URI:        https://github.com/vralle
 * License:           MIT
 * License URI:       LICENSE.txt
 * Text Domain:       vralle-lazyload
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/vralle/vralle-lazyload
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('VRALLE_LAZYLOAD_VERSION', '0.8.2');

/**
 * The core plugin class
 */
require_once plugin_dir_path(__FILE__) . 'app/plugin.php';

/**
 * Begins execution of the plugin.
 */
function run_vralle_lazyload()
{
    $plugin = new Plugin(plugin_basename(__FILE__));
    $plugin->run();
}
run_vralle_lazyload();
