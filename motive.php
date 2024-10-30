<?php
/**
 * (C) 2023 Motive Commerce Search Corp S.L. <info@motive.co>
 *
 * This file is part of Motive Commerce Search.
 *
 * This file is licensed to you under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Motive (motive.co)
 * @copyright (C) 2023 Motive Commerce Search Corp S.L. <info@motive.co>
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.motive.co
 * @since             1.0.0
 * @package           Motive
 *
 * @wordpress-plugin
 * Plugin Name:       Motive Commerce Search
 * Plugin URI:        https://www.motive.co
 * Description:       Commerce search that you and your shoppers can trust. Incredible design. Intuitive interface. Powerful customisations. Tracker and cookie-free.
 * Version:           1.32.0
 * Author:            Motive
 * Author URI:        https://www.motive.co/about-us
 * License:           Apache License, Version 2.0
 * License URI:       http://www.apache.org/licenses/LICENSE-2.0
 * Text Domain:       motive-commmerce-search
 * Domain Path:       /languages
 */
require_once __DIR__ . '/vendor/autoload.php';

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MOTIVE_VERSION', '1.32.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-motive-activator.php
 */
function activate_motive() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-motive-activator.php';
	Motive_Activator::activate();
}


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-motive-deactivator.php
 */
function deactivate_motive() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-motive-deactivator.php';
	Motive_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_motive' );
register_deactivation_hook( __FILE__, 'deactivate_motive' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-motive.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_motive() {

	$plugin = new Motive();
	$plugin->run();
}
run_motive();
