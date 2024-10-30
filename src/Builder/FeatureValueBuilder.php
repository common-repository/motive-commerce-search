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

use Motive\Woocommerce\Model\FeatureValue;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class FeatureValueBuilder {

	/**
	 * Returns the product's features
	 *
	 * @param int $product_id
	 * @param boolean $is_simple
	 *
	 * @return FeatureValue[] array of features values
	 */
	public static function fetch_for_product( $product_id, $raw_product_attributes, $is_simple ) {
		if ( ! $raw_product_attributes ) {
			return array();
		}
		$product_id = (int) $product_id;
		return self::parse_product_attributes( maybe_unserialize( $raw_product_attributes ), $product_id, $is_simple );
	}

	/**
	 * Parse product features from unserialized meta value
	 *
	 * @param Object[] $product_attributes
	 * @param int $product_id
	 * @param boolean $is_simple
	 *
	 * @return FeatureValue[] array of features values
	 */
	private static function parse_product_attributes( $product_attributes, $product_id, $is_simple ) {
		if ( ! is_array( $product_attributes ) && ! is_object( $product_attributes ) ) {
			return array();
		}
		$features = array();
		foreach ( $product_attributes as $product_attribute ) {
			if ( $product_attribute['is_variation'] && ! $is_simple ) {
				continue;
			}
			$id     = sanitize_title( $product_attribute['name'] );
			$name   = $product_attribute['name'];
			$values = array();

			if ( $product_attribute['is_taxonomy'] ) {
				$values = self::get_values_from_global_taxonomy( $product_id, $name );
			} else {
				$values = explode( '|', $product_attribute['value'] );
			}

			$key = AttributeBuilder::get_feature_key( $id );
			if ( ! isset( $features[ $key ] ) ) {
				$features[ $key ] = new FeatureValue( $key );
			}
			foreach ( $values as $value ) {
				$features[ $key ]->addValue( trim( $value ) );
			}
		}
		return array_values( $features );
	}

	/**
	 * Obtain global attributes values.
	 *
	 * @param int $product_id
	 * @param string $name
	 *
	 * @return String[] array of values
	 */
	private static function get_values_from_global_taxonomy( $product_id, $name ) {
		global $wpdb;
		$values             = array();
		$applied_attributes = $wpdb->get_results(
			"
            SELECT t.name
            FROM {$wpdb->get_blog_prefix()}terms t
                INNER JOIN {$wpdb->get_blog_prefix()}term_taxonomy tt ON tt.term_id = t.term_id
                INNER JOIN {$wpdb->get_blog_prefix()}term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tt.taxonomy='$name' AND tr.object_id=$product_id"
		);
		foreach ( $applied_attributes as $applied_attribute ) {
			$values[] = html_entity_decode( $applied_attribute->name );
		}
		return $values;
	}
}
