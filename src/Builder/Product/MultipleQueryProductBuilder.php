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

namespace Motive\Woocommerce\Builder\Product;

use Motive\Woocommerce\Builder\ProductBuilder;
use Motive\Woocommerce\Builder\TaxonomyBuilder;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * Class ProductBuilder
 * @package Motive\Woocommerce\Builder
 */
class MultipleQueryProductBuilder extends ProductBuilder {

	/**
	 * @param int $from_id_product
	 * @return Traversable<array> rows
	 */
	protected function query_products( $from_id_product = 0 ) {
		global $wpdb;
		do {
			$row_count = 0;

			$sql = $this->build_products_query( $from_id_product );

			$rows = $wpdb->get_results( $sql );
			$row  = &$rows[ $row_count ];

			$postmeta_sql = $this->get_postmeta_sql_from_rows( $rows, static::POSTMETA_KEYS );

			foreach ( $wpdb->get_results( $postmeta_sql ) as $postmeta_row ) {
				while ( $row->p_id < $postmeta_row->pm_postid ) {
					$from_id_product = $row->p_id;
					yield self::create_missing_row_fields( $row, static::POSTMETA_KEYS );
					++$row_count;
					$row = &$rows[ $row_count ];
				}

				$postmeta_key = "p$postmeta_row->pm_metakey";

				if ( ! property_exists( $row, $postmeta_key ) ) {
					$row->$postmeta_key = $postmeta_row->pm_metavalue;
				}
			}
			$from_id_product = $row->p_id;
			yield self::create_missing_row_fields( $row, static::POSTMETA_KEYS );
			++$row_count;
		} while ( $this->product_batch_size === $row_count );
	}


	/**
	 * To mimic wpdb behaviour, non existing fields should have a null value.
	 * @param object $row
	 * @param array $postmeta_keys
	 * @return object
	 */
	private function create_missing_row_fields( $row, $postmeta_keys ) {
		foreach ( $postmeta_keys as $postmeta_key ) {
			$row_key = "p$postmeta_key";
			if ( ! property_exists( $row, $row_key ) ) {
				$row->$row_key = null;
			}
		}
		return $row;
	}

	/**
	 * @param array $rows
	 * @param array $postmeta_keys
	 * @return string
	 */
	private function get_postmeta_sql_from_rows( $rows, $postmeta_keys ) {
		global $wpdb;

		$postmeta_keys_sql = "'" . join( "', '", $postmeta_keys ) . "'";
		$ids               = join( ',', array_column( $rows, 'p_id' ) );
		return "
			SELECT
					pm.post_id AS pm_postid,
					pm.meta_key AS pm_metakey,
					pm.meta_value AS pm_metavalue
			FROM {$wpdb->get_blog_prefix()}postmeta AS pm
			WHERE pm.post_id IN ($ids) AND pm.meta_key IN ($postmeta_keys_sql)
			ORDER BY pm.post_id, pm.meta_key, pm.meta_id DESC
		";
	}

	/**
	 * @param int $from_id_product
	 * @param int $limit
	 * @return string
	 */
	private function build_products_query( $from_id_product ) {
		global $wpdb;

		$relevant_taxonomies = TaxonomyBuilder::get_relevant_taxonomies();
		$inner_join_langs    = $this->language_manager->get_inner_join_products_lang();
		$from_id_product     = (int) $from_id_product;
		return "
      SELECT
          p.ID           AS p_id,
          p.post_title   AS p_name,
          p.post_content AS p_desc,
          p.post_excerpt AS p_short_desc
          $relevant_taxonomies->select_statement
      FROM {$wpdb->get_blog_prefix()}posts AS p
      $inner_join_langs
      $relevant_taxonomies->join_statement
      WHERE
          p.post_type='PRODUCT'
          AND p.ID > $from_id_product
          AND p.post_status = 'publish'
          $relevant_taxonomies->where_statement
      ORDER BY
          p.ID
      LIMIT {$this->product_batch_size};
    ";
	}
}
