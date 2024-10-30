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

namespace Motive\Woocommerce\Builder\Attribute;

use Motive\Woocommerce\Builder\AttributeBuilder;
use Motive\Woocommerce\Model\Attribute;
use Motive\Woocommerce\Model\Feature;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * This attribute builder will look for attributes & features inside term_taxonomy table, which
 * stores only global attributes & features. Very fast, but not able to find local attributes & features
 */
class FastAttributeBuilder extends AttributeBuilder {
	/**
	 * Fetch all attributes & features into local public vars
	 */
	public function fetch() {
		global $wpdb;
		$prefix = $wpdb->get_blog_prefix();

		$taxonomies_applying_as_attribute = $wpdb->get_col( "SELECT meta_key FROM {$prefix}postmeta WHERE meta_key LIKE 'attribute_%' GROUP BY meta_key" );

		$product_taxonomies = $wpdb->get_results( "SELECT taxonomy as 'taxonomy', SUM(count) as 'count' FROM {$prefix}term_taxonomy WHERE taxonomy LIKE 'pa_%' GROUP BY taxonomy" );

		$features        = array();
		$attributes      = array();
		$feature_counter = array();
		foreach ( $product_taxonomies as $product_taxonomy ) {
			$id         = sanitize_title( $product_taxonomy->taxonomy );
			$feature_id = self::get_feature_key( $id );
			$name       = $this->language_manager->get_label( $id, array( 'is_taxonomy' => true ) );

			$features[ $feature_id ]        = new Feature( $feature_id, $name );
			$feature_counter[ $feature_id ] = intval( $product_taxonomy->count );

			if ( in_array( "attribute_$product_taxonomy->taxonomy", $taxonomies_applying_as_attribute, true ) ) {
				$attributes[ $id ] = new Attribute( self::get_attribute_key( $id ), $name, false );
			}
		}

		$this->finalize_attributes_retrieval( $attributes, $features, $feature_counter );
	}
}
