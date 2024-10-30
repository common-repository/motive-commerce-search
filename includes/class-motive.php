<?php
use Motive\Woocommerce\PluginUpdateManager;
use Motive\Woocommerce\ScriptLoader;
use Motive\Woocommerce\Tagging;
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
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.motive.co
 * @since      1.0.0
 *
 * @package    Motive
 * @subpackage Motive/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Motive
 * @subpackage Motive/includes
 * @author     Motive <motive@motive.co>
 */
class Motive {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Motive_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'MOTIVE_VERSION' ) ) {
			$this->version = MOTIVE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'motive-commerce-search';

		$this->load_dependencies();
		PluginUpdateManager::init();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Motive_Loader. Orchestrates the hooks of the plugin.
	 * - Motive_I18n. Defines internationalization functionality.
	 * - Motive_Admin. Defines all hooks for the admin area.
	 * - Motive_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-motive-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-motive-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-motive-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-motive-public.php';

		$this->loader = new Motive_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Motive_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Motive_I18n();
		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Motive_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_menu' );

		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'add_endpoints' );

		// Add filter to add type module to configuration-page.js
		$this->loader->add_filter( 'script_loader_tag', $plugin_admin, 'add_type_module', 10, 3 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Motive_Public( $this->get_plugin_name(), $this->get_version() );
		// Adding preload, layer initialization & interoperability scripts and shopper prices URL.
		ScriptLoader::init( $this->loader, $this->plugin_name, $this->version );

		// Initialize tagging.
		Tagging::init( $this->loader );

		// Adding action to `rest_api_init` to add our endpoints
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'add_endpoints' );

		// This filter is called before WordPress dispatch the request, which in wp context mean both
		// matching route with registered rest endpoints & calling its endpoints. If we try to add
		// debug after this filter, debug wont apply, as response has been calculated. And, as for
		// activate debug we will need to check the token, seems the best place to check access
		$this->loader->add_filter( 'rest_pre_dispatch', $plugin_public, 'check_access', 11, 4 );

		// If we add endpoints using WordPress rest engine, we won't be able to manage header sending,
		// and therefore we can't split the feed call. Using this filter, we can add custom endpoints
		// and sending echo's before WordPress send headers. Used also to check security.
		// https://developer.wordpress.org/reference/hooks/rest_pre_serve_request/
		$this->loader->add_filter( 'rest_pre_serve_request', $plugin_public, 'intercept_for_feed_endpoint', 11, 4 );

		// woocommerce_add_to_cart_fragments manages what woocommerce will return on ajax adding to cart.
		$this->loader->add_filter( 'woocommerce_add_to_cart_fragments', $plugin_public, 'add_cart_count_fragment' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Motive_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
