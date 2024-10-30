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

use Motive\Woocommerce\Model\Variation;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class VariationBuilder {

	protected $image_builder;
	protected $price_builder;
	protected $language_manager;

	public function __construct( ImageBuilder $image_builder, PriceBuilder $price_builder, $language_manager ) {
		$this->image_builder    = $image_builder;
		$this->price_builder    = $price_builder;
		$this->language_manager = $language_manager;
	}

	/**
	 * Returns in case them exists, a list of variations for the selected product
	 *
	 * @param int $product_id
	 * @param string $default_attributes_raw
	 * @return Variation[] array of product's variations
	 */
	public function fetch_for( $product_id, $product_row ) {
		global $wpdb;

		$post_meta_keys = array(
			'_sku',
			'_stock_status',
			'_stock',
			'_manage_stock',
			'_regular_price',
			'_sale_price',
			'_sale_price_dates_from',
			'_sale_price_dates_to',
			'_children',
			'_thumbnail_id',
			'_tax_status',
			'_tax_class',
		);

		$post_meta_sql = PostMetaSqlBuilder::get_post_meta_sql( $post_meta_keys, $wpdb->get_blog_prefix(), 'LEFT', 'v' );
		$results       = $wpdb->get_results(
			"
            SELECT
                p.ID                        AS v_id,
                p.post_title                AS v_name,
                p.post_content              AS v_desc,
                p.post_excerpt              AS v_short_desc,
                $post_meta_sql->select_statement
            FROM {$wpdb->get_blog_prefix()}posts AS p
            $post_meta_sql->join_statement
            WHERE p.post_type='PRODUCT_VARIATION' AND p.post_parent = $product_id AND p.post_status = 'publish'
            ORDER BY p.menu_order;
        "
		);
		$variations    = array();

		$default_attributes_raw = maybe_unserialize( $product_row->p_default_attributes );
		$default_attributes     = $this->normalize_attr_keys( $default_attributes_raw );
		// Used to get only the first default variation match
		$default_found = false;
		foreach ( $results as $r ) {
			$v      = new Variation();
			$v->id  = $r->v_id;
			$v->url = get_permalink( $v->id );

			$v->images    = array();
			$thumbnail_id = null !== $r->v_thumbnail_id ?
				$r->v_thumbnail_id :
				$this->language_manager->get_thumbnail_id( $r->v_id );

			if ( ! empty( $thumbnail_id ) ) {
				$v->images = $this->image_builder->get_from_attachment_ids( array( $thumbnail_id ) );
			}

			$v->sku = $r->v_sku;

			$availability_builder = new AvailabilityBuilder();
			$v->availability      = $availability_builder->build_from(
				(int) $r->v_stock,
				(string) $r->v_manage_stock,
				(string) $r->v_stock_status
			);

			$v->price   = $this->price_builder->get_variation_price( $r, $product_row );
			$v->on_sale = ! empty( $v->price->on_sale ) && $v->price->regular !== $v->price->on_sale;

			// Variation attributes
			$attributes = AttributeValueBuilder::fetch_for_variation( $v->id );
			foreach ( $attributes as $attribute ) {
				$field_name     = $attribute->key;
				$v->$field_name = $attribute->value;
			}

			if ( ! $default_found ) {
				$default_found = $this->is_default_variation( $v, $default_attributes );
				$v->is_default = $default_found;
			} else {
				$v->is_default = false;
			}
			$v = apply_filters( 'motive_feed_variation', $v );

			$variations[] = $v;
		}

		if ( ! $default_found && count( $variations ) > 0 ) {
			$variations[0]->is_default = true;
		}

		return $variations;
	}

	/**
	 * The list of key normalized default attributes.
	 *
	 * @param int $product_id
	 * @return array array of key normalized default atributes
	 */
	private function normalize_attr_keys( $default_attributes_raw ) {
		if ( ! $default_attributes_raw ) {
			return array();
		}
		$default_attributes = array();
		foreach ( $default_attributes_raw as $id => $slug ) {
			$value = AttributeValueBuilder::try_get_values_from_global_taxonomy( $id, $slug );
			$default_attributes[ AttributeBuilder::get_attribute_key( $id ) ] = $value;
		}

		return $default_attributes;
	}

	/**
	 * Determines if the variation attributes match the default attributes
	 *
	 * @param Variation $variation
	 * @param array $default_attributes
	 * @return bool true matches, false doesn't match
	 */
	private function is_default_variation( $variation, $default_attributes ) {
		foreach ( $default_attributes as $attr => $value ) {
			if ( ! isset( $variation->$attr ) || $variation->$attr !== $value ) {
				return false;
			}
		}
		return $variation->availability->allow_order;
	}
}
