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

use Motive\Woocommerce\Model\XResultPrice;
use Motive\Woocommerce\Builder\PriceBuilder;
use Motive\Woocommerce\Config;
use Motive\Woocommerce\LanguageManager;

require_once __DIR__ . '/../../../../../wp-load.php';

header( 'Content-Type: application/json' );
if ( ! wp_verify_nonce( $_GET['nonce'], 'motive-endpoint' ) ) {
	header( 'HTTP/1.1 401 Unauthorized' );
	die( '[]' );
}
if ( ! Config::get_shopper_prices() ) {
	header( 'HTTP/1.1 404 Not Found' );
	die( '[]' );
}
if ( isset( $_GET['action'] ) && 'price_rates' === $_GET['action'] ) {
	echo wp_json_encode( get_price_rates() );
} else {
	echo wp_json_encode( get_shopper_prices() );
}

function get_price_rates() {
	$price_builder = new PriceBuilder();
	$user_tax_rate = $price_builder->get_tax_rate_range();
	$sync_tax_rate = json_decode( Config::get_last_sync_info()[ LanguageManager::get_instance()->current_lang ], true );
	if ( ! $sync_tax_rate ) {
		$sync_tax_rate = array(
			'min' => 1,
			'max' => 1,
		);
	}

	return array(
		$user_tax_rate['min'] / $sync_tax_rate['min'],
		$user_tax_rate['max'] / $sync_tax_rate['max'],
	);
}

function get_shopper_prices() {
	$json = file_get_contents( 'php://input' );
	$data = json_decode( $json );
	if ( empty( $data ) || ! is_array( $data ) ) {
		die( '[]' );
	}
	foreach ( $data as &$product ) {
		try {
			$woo_product = new \WC_Product( $product->id );

			if ( 'variable' !== \WC_Product_Factory::get_product_type( $product->id ) ) {
				$product->price = XResultPrice::build(
					\wc_get_price_to_display( $woo_product, array( 'price' => $woo_product->get_regular_price() ) ),
					\wc_get_price_to_display( $woo_product, array( 'price' => $woo_product->get_price() ) )
				);
				$product->price = apply_filters( 'motive_shopper_prices_product', $product->price, $product, $woo_product );
				continue;
			}
		} catch ( \Exception $e ) {
			$product->error = $e->getMessage();
			$product->price = apply_filters( 'motive_shopper_prices_product', $product->price, $product, $woo_product );
			continue;
		}
		foreach ( $product->variants as &$variation ) {
			try {
				$woo_variation    = new \WC_Product_Variation( $variation->id );
				$variation->price = XResultPrice::build(
					\wc_get_price_to_display( $woo_product, array( 'price' => $woo_variation->get_regular_price() ) ),
					\wc_get_price_to_display( $woo_product, array( 'price' => $woo_variation->get_price() ) )
				);
			} catch ( \Exception $e ) {
				$variation->error = $e->getMessage();
			}
			$variation->price = apply_filters( 'motive_shopper_prices_variation', $variation->price, $variation, $woo_variation, $woo_product );
		}
	}
	return $data;
}
