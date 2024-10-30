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

use Motive\Woocommerce\Token;
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.motive.co
 * @since      1.0.0
 *
 * @package    Motive
 * @subpackage Motive/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Motive
 * @subpackage Motive/admin
 * @author     Motive <motive@motive.co>
 */
class Motive_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @var      string    $hook    Current admin page.
	 * @since    1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( "toplevel_page_$this->plugin_name" === $hook ) {
			wp_enqueue_script( $this->plugin_name . '-configuration-page', 'https://assets.motive.co/configuration-page/configuration-page.js', array(), $this->version, false );
		}
	}

	/**
	 * Add type module to configuration-page.js
	 *
	 * @since    1.18.1
	 */
	public function add_type_module( $tag, $handle, $src ) {
		if ( 'motive-commerce-search-configuration-page' !== $handle ) {
			return $tag;
		}
		// phpcs:disable
		return '<script type="module" src="' . esc_url( $src ) . '"></script>';
		// phpcs:enable
	}

	/**
	 * Add menu option for Motive plugin.
	 */
	public function add_menu() {
		$motive_icon_file = file_get_contents( plugin_dir_path( __FILE__ ) . 'img/motive-logo-wp.svg' );
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$motive_icon = 'data:image/svg+xml;base64,' . base64_encode( $motive_icon_file );
		add_menu_page( 'Motive', 'Motive', 'manage_options', $this->plugin_name, array( $this, 'motive_admin' ), $motive_icon );
	}


	/**
	 * Register admin endpoints.
	 *
	 * @since    1.0.0
	 */
	public function add_endpoints() {
		// route url: domain.com/wp-json/motive/admin/regenerate-token
		// route url: domain.com/?rest_route=/motive/admin/regenerate-token
		register_rest_route(
			'motive',
			'admin/regenerate-token',
			array(
				'methods'             => 'GET',
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'callback'            => function () {
					return Token::regenerate();
				},
			)
		);
	}

	/**
	 * Defines admin view main file.
	 */
	public function motive_admin() {
			include plugin_dir_path( __FILE__ ) . 'partials/motive-admin-display.php';
	}
}
