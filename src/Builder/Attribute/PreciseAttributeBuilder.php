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
use Motive\Woocommerce\Builder\PostMetaSqlBuilder;
use Motive\Woocommerce\Builder\TaxonomyBuilder;
use Motive\Woocommerce\Model\Attribute;
use Motive\Woocommerce\Model\Feature;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * This attribute builder will look for attributes & features by iterating over all products. Very
 * slow, but only current know method to obtain all attributes & features, local ones included.
 */
class PreciseAttributeBuilder extends AttributeBuilder {

	/**
	 * Fetch all attributes & features into local public vars
	 */
	public function fetch() {
		global $wpdb;

		$post_meta_sql       = PostMetaSqlBuilder::get_post_meta_sql( array( '_product_attributes' ), $wpdb->get_blog_prefix(), 'INNER' );
		$relevant_taxonomies = TaxonomyBuilder::get_relevant_taxonomies();
		$inner_join_langs    = $this->language_manager->get_inner_join_products_lang();
		$prefix              = $wpdb->get_blog_prefix();
		$raw_products_metas  = $wpdb->get_results(
			"
        SELECT 
          $post_meta_sql->select_statement
          $relevant_taxonomies->select_statement
        FROM {$prefix}posts p
          $inner_join_langs
          $post_meta_sql->join_statement
          $relevant_taxonomies->join_statement
        WHERE 
          p.post_type='PRODUCT'
          $relevant_taxonomies->where_statement
      "
		);

		$features        = array();
		$attributes      = array();
		$feature_counter = array();
		foreach ( $raw_products_metas as $raw_product_meta ) {
			$product_attributes = maybe_unserialize( $raw_product_meta->p_product_attributes );
			if ( ! is_array( $product_attributes ) && ! is_object( $product_attributes ) ) {
				continue;
			}
			foreach ( $product_attributes as $id => $product_attribute ) {
				if ( '' === $id ) {
					continue;
				}
				$id   = sanitize_title( $id );
				$name = $this->language_manager->get_label( $id, $product_attribute );
				if ( $product_attribute['is_variation'] && '0' !== $raw_product_meta->p_is_variable ) {
					if ( empty( $attributes[ $id ] ) ) {
						$attributes[ $id ] = new Attribute( self::get_attribute_key( $id ), $name, false );
					}
				} else {
					$feature_id = self::get_feature_key( $id );
					if ( empty( $features[ $feature_id ] ) ) {
						$features[ $feature_id ]        = new Feature( $feature_id, $name );
						$feature_counter[ $feature_id ] = 1;
					} else {
						++$feature_counter[ $feature_id ];
					}
				}
			}
		}
		$this->finalize_attributes_retrieval( $attributes, $features, $feature_counter );
	}
}
