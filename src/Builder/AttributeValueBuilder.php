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

use Motive\Woocommerce\Model\AttributeValue;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class AttributeValueBuilder {

	private static $cache_values = array();
	/**
	 * Returns variation attributes
	 *
	 * @param int $variation_id
	 *
	 * @return AttributeValue[] array of attribute values
	 */
	public static function fetch_for_variation( $variation_id ) {
		$variation_id = (int) $variation_id;

		global $wpdb;

		$raw_variation_attributes = $wpdb->get_results(
			"
            SELECT pm.meta_key, pm.meta_value
            FROM {$wpdb->get_blog_prefix()}posts p
                INNER JOIN {$wpdb->get_blog_prefix()}postmeta pm ON p.ID = pm.post_id
            WHERE p.post_type='PRODUCT_VARIATION' AND pm.meta_key like 'attribute_%' AND p.ID=$variation_id
        "
		);
		if ( ! $raw_variation_attributes ) {
			return array();
		}
		$attributes = array();
		foreach ( $raw_variation_attributes as $raw_variation_attribute ) {
			$key   = $raw_variation_attribute->meta_key;
			$key   = str_replace( 'attribute_', '', $raw_variation_attribute->meta_key );
			$value = self::try_get_values_from_global_taxonomy( $key, $raw_variation_attribute->meta_value );

			$attributes[] = new AttributeValue( AttributeBuilder::get_attribute_key( $key ), $value );
		}
		return $attributes;
	}

		/**
	 * Obtain global attributes values.
	 *
	 * @param string $term_name
	 * @param string $term_slug
	 *
	 * @return string label of the slug if found, slug otherwise
	 */
	public static function try_get_values_from_global_taxonomy( $term_name, $term_slug ) {
		if ( isset( static::$cache_values[ $term_slug ] ) ) {
			return static::$cache_values[ $term_slug ];
		}
		static::$cache_values[ $term_slug ] = $term_slug;
		global $wpdb;
		$term_value = $wpdb->get_var(
			"
            SELECT t.name
            FROM {$wpdb->get_blog_prefix()}terms t
                INNER JOIN {$wpdb->get_blog_prefix()}term_taxonomy tt ON tt.term_id = t.term_id
            WHERE tt.taxonomy='$term_name' AND t.slug='$term_slug'"
		);
		if ( $term_value ) {
			static::$cache_values[ $term_slug ] = html_entity_decode( $term_value );
		}
		return static::$cache_values[ $term_slug ];
	}
}
