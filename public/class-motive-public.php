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

use Motive\Woocommerce\Config;
use Motive\Woocommerce\LanguageManager;
use Motive\Woocommerce\Token;
use Motive\Woocommerce\TimeLimit;
use Motive\Woocommerce\Builder\FeedBuilder;
use Motive\Woocommerce\Builder\InfoBuilder;
use Motive\Woocommerce\Builder\SchemaBuilder;

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.motive.co
 * @since      1.0.0
 *
 * @package    Motive
 * @subpackage Motive/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Motive
 * @subpackage Motive/public
 * @author     Motive <motive@motive.co>
 */
class Motive_Public {
	const NS                  = 'motive';
	const HEADER_MOTIVE_TOKEN = 'X_MOTIVE_TOKEN';

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * This function adds to add-to-cart return the number of elements in cart.
	 */
	public function add_cart_count_fragment( $fragments ) {
		if ( Config::is_enabled() ) {
			$fragments['meta#motive_cart_info'] = sprintf( '<meta id="motive_cart_info" data-products-count="%s" />', WC()->cart->cart_contents_count );
		}
		return $fragments;
	}

	/**
	 * Register public endpoints.
	 *
	 * @since    0.0.1
	 */
	public function add_endpoints() {
		// route url: domain.com/wp-json/motive/check
		// route url: domain.com/?rest_route=/motive/check
		register_rest_route(
			self::NS,
			'check',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					return array(
						'status' => 'ok',
					);
				},
			)
		);

		// route url: domain.com/wp-json/motive/info
		// route url: domain.com/?rest_route=/motive/info
		register_rest_route(
			self::NS,
			'info',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					return InfoBuilder::build();
				},
			)
		);

		// route url: domain.com/wp-json/motive/<lang>/schema
		// route url: domain.com/?rest_route=/motive/<lang>/schema
		register_rest_route(
			self::NS,
			'(?P<lang>.+)/schema',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function ( $data ) {
					$lang         = LanguageManager::get_instance();
					$lang->set_current_lang( $data->get_param( 'lang' ) );
					$schema_builder = new SchemaBuilder( $lang, get_woocommerce_currency() );
					return $schema_builder->build( $data );
				},
			)
		);

		// route url: domain.com/wp-json/motive/config
		// route url: domain.com/?rest_route=/motive/config
		register_rest_route(
			self::NS,
			'config',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					// Using $_GET instead of $data->has_param to be compatible with wp < 5.3.0.
					// phpcs:disable WordPress.Security.NonceVerification.Recommended
					return Config::export( isset( $_GET['all'] ), isset( $_GET['raw'] ) );
					// phpcs:enable WordPress.Security.NonceVerification.Recommended
				},
			)
		);
		// route url: domain.com/wp-json/motive/config
		// route url: domain.com/?rest_route=/motive/config
		register_rest_route(
			self::NS,
			'config',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => function ( $data ) {
					// phpcs:disable WordPress.Security.NonceVerification.Recommended
					return self::post_config( $data, isset( $_GET['if-unset'] ) );
					// phpcs:enable WordPress.Security.NonceVerification.Recommended
				},
			)
		);

		// route url: domain.com/wp-json/motive/config-if-unset
		// route url: domain.com/?rest_route=/motive/config-if-unset
		register_rest_route(
			self::NS,
			'config-if-unset',
			array(
				'methods'             => 'POST',
				'permission_callback' => '__return_true',
				'callback'            => function ( $data ) {
					$only_if_unset = true;
					return self::post_config( $data, $only_if_unset );
				},
			)
		);

		// If MCS is not enabled, there is no need to add following endpoint, related to layer.
		if ( ! Config::is_enabled() ) {
			return;
		}

		// route url: domain.com/wp-json/motive/front-config
		// route url: domain.com/?rest_route=/motive/front-config
		register_rest_route(
			self::NS,
			'front-config',
			array(
				'methods'             => 'GET',
				'permission_callback' => '__return_true',
				'callback'            => function () {
					if ( ! Config::is_enabled() ) {
						return;
					}
					return Config::get_front_config();
				},
			)
		);
	}

	/**
	 * Filters the pre-calculated result of a REST API dispatch request.
	 * For wp, dispatch is the process of matching route with registered REST endpoints and calling the
	 * callback. In other words, after this filter, wp will make all calls, so we need to use this filter
	 * to add error reporting.
	 * We use it for:
	 * - Checking access with token in all motive protected routes (all but check & front-config)
	 * - Populate config with query params, to be able to live change config values
	 * - Check error reporting status, and activate or disable it.
	 *
	 * @param mixed           $result  Response to replace the requested version with. Can be anything
	 *                                 a normal endpoint can return, or null to not hijack the request.
	 * @param WP_REST_Server  $server  Server instance.
	 * @param WP_REST_Request $request Request used to generate the response.
	 */
	public function check_access( $result, $server, $request ) {
		// If request don't relate to a motive route apart from check or front-config, we stop here and let wp to fully manage request.
		if ( strpos( $request->get_route(), '/motive/' ) === false ||
			0 === strpos( $request->get_route(), '/motive/check' ) ||
			0 === strpos( $request->get_route(), '/motive/front-config' ) ||
			0 === strpos( $request->get_route(), '/motive/admin' )
		) {
			return $result;
		}
		// We're in a motive protected route. Checking token.
		if ( ! Token::check( $request->get_header( self::HEADER_MOTIVE_TOKEN ) ) ) {
			header( 'HTTP/1.1 401 Unauthorized' );
			header( 'Content-Type:application/json; charset=utf-8' );
			echo( \wp_json_encode( array( 'error' => 'Unauthorized. Invalid or missed security token' ) ) );
			die();
		}
		// If we are in a motive related route, and token is valid, we will populate query params in
		// Config, to avoid overwriting configuration values with query params.
		$query = $request->get_params();
		Config::set_query_params( $query );
		// If `activate_error_reporting` configuration value is not "1", we don't do anything more.
		if ( Config::get_activate_error_reporting() !== '1' ) {
			return $result;
		}
		// Only if we are in a motive related route, we will check if a "debug" query param is present.
		if ( isset( $query['debug'] ) ) {
			// If debug query param is present, we will enable error reporting to check what can be
			// happening with our endpoints in json response.
			// phpcs:disable
			ini_set( 'display_errors', '1' );
			error_reporting( E_ALL );
			// phpcs:enable
			// Also we will enable print sql errors
			global $wpdb;
			$wpdb->show_errors( true );
		} else {
			// In case debug query param is not present, as we want to avoid warnings, notices or infos
			// which can break json, we will disable "display_errors"
			// phpcs:disable
			ini_set( 'display_errors', '0' );
			// phpcs:enable
		}
		return $result;
	}

	/**
	 * This function intercepts WordPress rest engine before headers or any info is sent, allowing
	 * us to hijack response and send data using echo.
	 * Note that when wp calls this filter, the response has been previously calculated if the route
	 * matches any of the registered rest routes ($result parameter).
	 *
	 * @param bool $served - Boolean given by WordPress which will be used to know if the request was served.
	 * @param WP_HTTP_Response $result - Standard response which can be used to send info (other endpoints use it internally).
	 * @param WP_REST_Request $request - Object with current request info.
	 *
	 * @return boolean indicating if any content was served.
	 */
	public function intercept_for_feed_endpoint( $served, $result, $request ) {
		$route        = $request->get_route();
		$query_params = $request->get_params();
		// route url: domain.com/wp-json/motive/<lang>/feed
		// route url: domain.com/?rest_route=/motive/<lang>/feed
		if ( preg_match( '/\/motive\/([^\/]+)\/feed/', $route, $matches ) ) {
			$lang_code = $matches[1];
			$this->write_feed( $lang_code, $query_params );
			return true;
		}

		return false;
	}

	/**
	 * This function manages feed echoing, including headers.
	 */
	private static function write_feed( $lang_code, $query_params ) {
		header( 'HTTP/1.1 200 Ok' );
		$lang = LanguageManager::get_instance();
		$lang->set_current_lang( $lang_code );
		$feed_builder = new FeedBuilder( $lang, get_woocommerce_currency() );

		$from_id = 0;
		if ( ! empty( $query_params['from_id'] ) ) {
			$from_id = (int) $query_params['from_id'];
		}
		$max_time = Config::get_time_limit();
		if ( ! empty( $query_params['max_time'] ) ) {
			$max_time = (int) $query_params['max_time'];
		}
		$feed       = $feed_builder->build( $from_id );
		$time_limit = new TimeLimit( $max_time, $max_time );
		$last_id    = null;

		$feed->jsonStream(
			0,
			function ( $id ) use ( $time_limit, &$last_id, $lang, $query_params ) {
				$id_product = (int) $id; // Id can have variant separator '-'
				// Split feed between variants not supported
				if ( $last_id === $id_product ) {
						return false;
				}
				$last_id = $id_product;
				if ( $time_limit->remaining_time() > 5 ) {
						return false;
				}
				$query_params['from_id'] = $last_id;

				$rest_url      = $lang->get_language_url( $lang->current_lang, 'feed' );
				$next_page_url = add_query_arg( $query_params, $rest_url );
				return $next_page_url;
			}
		);
	}

	/**
	 * Common function to manage both config post endpoints.
	 * @param array $data
	 * @param boolean $only_if_unset
	 * @return WP_REST_Response|array
	 */
	private function post_config( $data, $only_if_unset = false ) {
		$body = $data->get_json_params();
		if ( ! is_array( $body ) ) {
			$response = new WP_REST_Response( array( 'error' => 'Bad request body.' ) );
			$response->set_status( 400 );
			return $response;
		}
		return Config::import( $body, $only_if_unset );
	}
}
