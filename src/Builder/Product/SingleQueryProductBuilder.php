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

use Motive\Woocommerce\Builder\PostMetaSqlBuilder;
use Motive\Woocommerce\Builder\ProductBuilder;
use Motive\Woocommerce\Builder\TaxonomyBuilder;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * Class ProductBuilder
 * @package Motive\Woocommerce\Builder
 */
class SingleQueryProductBuilder extends ProductBuilder {

	/**
	 * @param int $from_id_product
	 * @return Traversable<array> rows
	 */
	protected function query_products( $from_id_product = 0 ) {
		global $wpdb;

		do {
			$row_count = 0;
			$sql       = $this->build_query( $from_id_product, static::POSTMETA_KEYS );
			foreach ( $wpdb->get_results( $sql ) as $row ) {
				$from_id_product = $row->p_id;
				yield $row;
				++$row_count;
			}
		} while ( $this->product_batch_size === $row_count );
	}

		/**
	 * @param int $from_id_product
	 * @param int $limit
	 * @param array $postmeta_keys
	 * @return string
	 */
	private function build_query( $from_id_product, $postmeta_keys ) {
		global $wpdb;

		$post_meta_sql       = PostMetaSqlBuilder::get_post_meta_sql( $postmeta_keys, $wpdb->get_blog_prefix() );
		$relevant_taxonomies = TaxonomyBuilder::get_relevant_taxonomies();
		$inner_join_langs    = $this->language_manager->get_inner_join_products_lang();
		$from_id_product     = (int) $from_id_product;
		return "
      SELECT
          p.ID           AS p_id,
          p.post_title   AS p_name,
          p.post_content AS p_desc,
          p.post_excerpt AS p_short_desc,
          $post_meta_sql->select_statement
          $relevant_taxonomies->select_statement
      FROM {$wpdb->get_blog_prefix()}posts AS p
      $inner_join_langs
      $post_meta_sql->join_statement
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
