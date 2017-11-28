<?php
use Vralle\Lazyload\App\Activator;
use Vralle\Lazyload\App\Deactivator;
use Vralle\Lazyload\App\Plugin;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://author.uri
 * @since             0.1.0
 * @package           Vralle_Lazyload
 *
 * @wordpress-plugin
 * Plugin Name:       VRALLE.Lazyload
 * Plugin URI:        https://plugin.uri
 * Description:       Lazy loading images to speed up loading pages and reduce the load on the server. Images are loaded when they get to the screen. Uses lazysizes.js
 * Version:           0.5.0
 * Author:            Vitaliy Ralle
 * Author URI:        https://author.uri
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       vralle-lazyload
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('VRALLE_LAZYLOAD_VERSION', '0.5.0');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-vralle-lazyload-activator.php
 */
function activate_vralle_lazyload()
{
    require_once plugin_dir_path(__FILE__) . 'app/activator.php';
    Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-vralle-lazyload-deactivator.php
 */
function deactivate_vralle_lazyload()
{
    require_once plugin_dir_path(__FILE__) . 'app/deactivator.php';
    Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_vralle_lazyload');
register_deactivation_hook(__FILE__, 'deactivate_vralle_lazyload');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'app/plugin.php';

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
    $plugin = new Plugin();
    $plugin->run();
}
run_vralle_lazyload();
