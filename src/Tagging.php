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

use Motive\Woocommerce\Builder\MetadataBuilder;

class Tagging {
	public static function init( $loader ) {
		new Tagging( $loader );
	}

	public function __construct( $loader ) {
		// woocommerce_add_to_cart action is fired when a product is added to cart.
		$loader->add_action( 'woocommerce_add_to_cart', $this, 'tag_add2cart', 10, 6 );
	}


	// phpcs:disable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
	/**
	 * Function for `woocommerce_add_to_cart` action-hook, to being able to launch a request for
	 * tag the add to cart event, only if it cames from motive layer.
	 *
	 * @param string  $cart_id          ID of the item in the cart.
	 * @param integer $product_id       ID of the product added to the cart.
	 * @param integer $request_quantity Quantity of the item added to the cart.
	 * @param integer $variation_id     Variation ID of the product added to the cart.
	 * @param array   $variation        Array of variation data.
	 * @param array   $cart_item_data   Array of other cart item data.
	 *
	 * @return void
	 */
	public function tag_add2cart( $cart_id, $product_id, $request_quantity, $variation_id, $variation, $cart_item_data ) {
		// phpcs:enable Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed
		try {
			$tag_click_query_value = $this->get_add2cart_click_uuid();
			if ( null === $tag_click_query_value || ! Config::is_enabled() ) {
				return;
			}
			$tagging_add2cart_url = rtrim( Config::get_tagging_base_url(), '/' ) . '/products/add-to-cart';
			if ( ! filter_var( $tagging_add2cart_url, FILTER_VALIDATE_URL ) ) {
				return;
			}
			$element_added = wc_get_product( $variation_id ? $variation_id : $product_id );

			$payload = array(
				'clickUUID' => $tag_click_query_value,
				'productId' => (string) $product_id,
				'quantity'  => $request_quantity,
				'link'      => $element_added->get_permalink(),
				'price'     => $element_added->get_price(),
				'reference' => $element_added->get_sku(),
			);

			$images = wp_get_attachment_image_src( $element_added->get_image_id(), Config::get_image_size() );
			if ( ! empty( $images ) ) {
				$payload['image'] = $images[0];
			}
			if ( ! empty( $variation_id ) ) {
				$payload['variationId'] = (string) $variation_id;
			}
			wp_remote_post(
				$tagging_add2cart_url,
				array(
					'method'   => 'POST',
					'blocking' => false,
					'timeout'  => ( (int) Config::get_tagging_timeout() ) / 1000,
					'body'     => wp_json_encode( $payload ),
					'headers'  => array(
						'Content-Type'  => 'application/json',
						'x-engine-id'   => Config::get_engine_id()[ LanguageManager::get_instance()->current_lang ],
						'x-api-version' => '1',
						'user-agent'    => MetadataBuilder::get_source(),
					),
				)
			);
		// phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		} catch ( \Throwable $e ) {
			// Silenced exceptions to not affect shopper's add to cart.
		}
		// phpcs:enable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
	}

	/**
	 * Helper function to return the value of the param which indicates we came from a motive layer
	 * request, or null if we don't came from a MCS layer.
	 *
	 * @return  string|null
	 */
	private function get_add2cart_click_uuid() {
		$clickuuid_query_name = 'mot_tcid';
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ $clickuuid_query_name ] ) ) {
			return $_GET[ $clickuuid_query_name ];
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended
		if ( ! array_key_exists( 'HTTP_REFERER', $_SERVER ) ) {
			return null;
		}

		$query_params_str = wp_parse_url( $_SERVER['HTTP_REFERER'], PHP_URL_QUERY );
		if ( null === $query_params_str ) {
			return null;
		}

		parse_str( $query_params_str, $query_params );
		if ( empty( $query_params[ $clickuuid_query_name ] ) ) {
			return null;
		}
		return $query_params[ $clickuuid_query_name ];
	}
}
