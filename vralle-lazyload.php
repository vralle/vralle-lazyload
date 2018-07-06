<?php
use Vralle\Lazyload\App;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/vralle/vralle-lazyload
 * @since             0.1.0
 * @package           Vralle_Lazyload
 *
 * @wordpress-plugin
 * Plugin Name:       VRALLE.Lazyload
 * Description:       Lazy loading images to speed up loading pages and reduce the load on the server. Images are loaded when they get to the screen. Uses lazysizes.js
 * Version:           0.8.1
 * Author:            Vitaliy Ralle
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

define('VRALLE_LAZYLOAD_VERSION', '0.8.1');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vralle-lazyload-activator.php
 */
function activate_vralle_lazyload()
{
    require_once plugin_dir_path(__FILE__) . 'app/activator.php';
    app\Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vralle-lazyload-deactivator.php
 */
function deactivate_vralle_lazyload()
{
    require_once plugin_dir_path(__FILE__) . 'app/deactivator.php';
    App\Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_vralle_lazyload');
register_deactivation_hook(__FILE__, 'deactivate_vralle_lazyload');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once plugin_dir_path(__FILE__) . 'app/plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    0.1.0
 */
function run_vralle_lazyload()
{
    $plugin = new App\Plugin(plugin_basename(__FILE__));
    $plugin->run();
}
run_vralle_lazyload();
