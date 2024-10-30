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

namespace Motive\Woocommerce;

/**
 * ScriptLoader abstract class is the place where we implement in different ways the layer
 * initialization scripts with its preloads, and also the interoperability script.
 */
abstract class ScriptLoader {
	protected $loader;
	protected $plugin_name;
	protected $version;
	protected $script_url;
	protected $extra_script_attr = '';

	public static function init( $loader, $plugin_name, $version ) {
		$script_loader_class = Config::get_script_loader();
		new $script_loader_class( $loader, $plugin_name, $version );
	}

	protected function __construct( $loader, $plugin_name, $version ) {
		$this->loader      = $loader;
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->script_url  = plugins_url() . "/$plugin_name/public/js/motive-public.js";
		$this->loader->add_action( 'init', $this, 'check_plugins' );

		if ( $this->should_add_layer() ) {
			// In order to add preload for motive js & layer config endpoint, wp_head action added with
			// priority 1, to add it as soon as posible to page html.
			$this->loader->add_action( 'wp_head', $this, 'preload_links', 1 );
			$this->add_layer_js();
		}

		if ( $this->should_add_interoperability() ) {
			$this->add_interoperability_js();
		}
	}

	public function check_plugins() {
		if ( function_exists( 'rocket_clean_home' ) && '1' === Config::get_wp_rocket_bypass() ) {
			// https://docs.wp-rocket.me/article/1349-delay-javascript-execution
			$this->extra_script_attr = 'nowprocket';
		}
	}

	private function should_add_layer() {
		return Config::is_enabled();
	}

	private function should_add_interoperability() {
		// To support old Safari versions, only check if header is defined.
		if ( array_key_exists( 'HTTP_SEC_FETCH_DEST', $_SERVER ) && 'iframe' !== $_SERVER['HTTP_SEC_FETCH_DEST'] ) {
			return false;
		}
		if ( empty( $_SERVER['HTTP_REFERER'] ) || Config::get_playboard_url() !== $_SERVER['HTTP_REFERER'] ) {
			return false;
		}
		return true;
	}

	abstract public function add_layer_js();
	abstract public function add_interoperability_js();

	/**
	 * Add preload links in head to improve configuration and layer load. These links are also used
	 * to obtain both urls front-side. Only will append if MCS is configured.
	 */
	public function preload_links() {
		printf( "<link id='motive-layer-js' rel='modulepreload' href='%s' as='script' crossorigin='anonymous'>", esc_url( Config::get_motive_x_url() ) );
		printf( "<link id='motive-config-url' rel='prefetch' href='%s' as='fetch'>", esc_url( get_rest_url( null, 'motive/front-config' ) ) );
	}

	/**
	 * Returns front & layer initialization data.
	 */
	public static function get_front_config() {
		$lang_manager = LanguageManager::get_instance();
		if ( ! function_exists( 'wc_get_cart_url' ) ) {
			require_once ABSPATH . 'wp-content/plugins/woocommerce/includes/wc-core-functions.php';
		}
		$motive = array(
			'initParams' => array(
				'xEngineId'                => Config::get_engine_id()[ $lang_manager->current_lang ],
				'lang'                     => $lang_manager->current_lang,
				'currency'                 => get_woocommerce_currency(),
				'triggerSelector'          => Config::get_trigger_selector(),
				'isolated'                 => (bool) Config::get_layer_isolated(),
				'cartUrl'                  => wc_get_cart_url(),
				'externalAddToCartTagging' => 'QUERY_PARAM' === Config::get_tagging_addtocart(),
			),
			'options'    => array(
				'shopperPrices'               => (bool) Config::get_shopper_prices(),
				'shopperPricesEndpointMethod' => Config::get_shopper_prices_endpoint_method(),
			),
			'endpoints'  => array(
				'shopperPrices' => plugins_url( 'public/shopper-prices/shopper-prices.php', __DIR__ ),
			),
			'nonce'      => wp_create_nonce( 'motive-endpoint' ),
		);

		return $motive;
	}
}
