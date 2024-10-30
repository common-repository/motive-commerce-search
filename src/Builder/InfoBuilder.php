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

namespace Motive\Woocommerce\Builder;

use Motive\Woocommerce\Config;
use Motive\Woocommerce\Model\CatalogInfo;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

use Motive\Woocommerce\LanguageManager;
use Motive\Woocommerce\Model\Info;
use Motive\Woocommerce\Model\Platform;

class InfoBuilder {
	/**
	 * Info builder from options.
	 * Returns info about current PrestaShop instance
	 * @param LanguageManager $lang_manager
	 * @return Info
	 */
	public static function build() {
		$lang_manager   = LanguageManager::get_instance();
		$info           = new Info();
		$info->source   = array(
			'version'            => MOTIVE_VERSION,
			'platform'           => Platform::build(),
			'first_install_date' => Config::get_first_activation_date(),
			'software'           => array(
				'name'    => 'WooCommerce',
				'version' => WC_VERSION,
				'modules' => self::get_module_list(),
			),
		);
		$info->options  = array(
			'default_language' => LanguageBuilder::from_code( $lang_manager->default_lang ),
			'default_currency' => CurrencyBuilder::from_code( get_woocommerce_currency() ),
			'shop'             => ShopBuilder::from_id_and_name( $lang_manager, get_current_blog_id(), get_bloginfo( 'name' ) ),
			'other_shops'      => array(),
			'image'            => array(
				'logo'     => self::get_logo(),
				'settings' => self::get_image_size_list(),
			),
		);
		$info->urls     = array(
			'check'           => self::controller_url( 'check', false ),
			'config'          => self::controller_url( 'config', false ),
			'config-if-unset' => self::controller_url( 'config-if-unset', false ),
			'info'            => self::controller_url( 'info', false ),
			'schema'          => self::controller_url( 'schema', $lang_manager ),
			'feed'            => self::controller_url( 'feed', $lang_manager ),
		);
		$info->metrics  = self::get_metrics();
		$info->catalogs = $lang_manager->get_language_list_with_locale();

		return $info;
	}

	/**
	 * Create the URL for the module controller for one or more languages.
	 * @param $name
	 * @param LanguageManager|false $lang_manager
	 * @return array|string
	 */
	protected static function controller_url( $name, $lang_manager ) {
		if ( ! $lang_manager ) {
			// We add a time flag to avoid getting a cached response.
			return add_query_arg( 'nocache', time(), get_rest_url( null, "motive/$name" ) );
		}
		$langs = $lang_manager->langs;
		$urls  = array();
		foreach ( $langs as $lang ) {
			$urls[ $lang ] = add_query_arg( 'nocache', time(), $lang_manager->get_language_url( $lang, $name ) );
		}

		return $urls;
	}

	/**
	 * Get installed modules
	 * @return array
	 */
	public static function get_module_list() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$ret = array();
		foreach ( get_plugins() as $plugin_src => $plugin_data ) {
			$ret[ $plugin_src ] = array(
				'name'    => $plugin_data['Name'],
				'version' => $plugin_data['Version'],
				'active'  => is_plugin_active( $plugin_src ),
			);
		}

		return $ret;
	}

	/**
	 * Get custom logo
	 * @return array|null
	 */
	public static function get_logo() {
		$logo_media_id = get_theme_mod( 'custom_logo' );
		$image         = wp_get_attachment_image_src( $logo_media_id, 'full', false );
		if ( false === $image ) {
			return null;
		}
		list( $url, $width, $height ) = $image;
		return array(
			'url'    => $url,
			'width'  => $width,
			'height' => $height,
		);
	}

	/**
	 * Get available product image sizes
	 * @return array
	*/
	public static function get_image_size_list() {
		$wp_additional_image_sizes    = wp_get_additional_image_sizes();
		$sizes                        = array();
		$get_intermediate_image_sizes = get_intermediate_image_sizes();

		// Create the full array with sizes and crop info
		foreach ( $get_intermediate_image_sizes as $_size ) {
			$current_size = array( 'name' => $_size );
			if ( 'thumbnail' === $_size || 'medium' === $_size || 'large' === $_size ) {
				$current_size['width']  = (int) get_option( $_size . '_size_w' );
				$current_size['height'] = (int) get_option( $_size . '_size_h' );

				$sizes[] = $current_size;
			} elseif ( isset( $wp_additional_image_sizes[ $_size ] ) ) {
				$current_size['width']  = (int) $wp_additional_image_sizes[ $_size ]['width'];
				$current_size['height'] = (int) $wp_additional_image_sizes[ $_size ]['height'];

				$sizes[] = $current_size;
			}
		}
		return $sizes;
	}

	/**
	 * Get catalog metrics.
	 *
	 * @return array
	 */
	public static function get_metrics() {
			global $wpdb;
			$relevant_taxonomies = TaxonomyBuilder::get_relevant_taxonomies();

			$searchable_products = $wpdb->get_var(
				"
          SELECT count(*)
          FROM {$wpdb->get_blog_prefix()}posts AS p
          $relevant_taxonomies->join_statement
          WHERE
              p.post_type='PRODUCT'
              AND p.post_status = 'publish'
              $relevant_taxonomies->where_statement
        "
			);
		return array(
			'searchable_products' => $searchable_products,
		);
	}
}
