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

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

abstract class TaxonomyBuilder {

	protected $taxonomies    = array();
	protected $taxonomy_name = '';

	public static $cache_relevant_taxonomies = null;

	/**
	 * TaxonomyBuilder constructor.
	 */
	public function __construct( $taxonomy_name ) {
		$this->taxonomy_name = $taxonomy_name;
		// Preload taxonomy
		foreach ( $this->fetch() as $taxonomy ) {
			$this->taxonomies[ $taxonomy->term_id ] = $taxonomy;
		}
	}

	/**
	 * Returns an array of all taxonomies of given $this->taxonomy_name in woocommerce
	 *
	 * @return array
	 */
	private function fetch() {
		global $wpdb;
		return $wpdb->get_results(
			"
            SELECT t.term_id, t.name, tt.parent
            FROM {$wpdb->get_blog_prefix()}term_taxonomy tt
                INNER JOIN {$wpdb->get_blog_prefix()}terms t ON t.term_id = tt.term_id
            WHERE tt.taxonomy = '$this->taxonomy_name'
        "
		);
	}

	/**
	 * Returns the product's raw taxonomies id's
	 *
	 * @param int $id_product
	 *
	 * @return object[] array of raw taxonomies
	 */
	protected function fetch_for_product( $id_product ) {
		global $wpdb;
		return $wpdb->get_results(
			"
            SELECT t.term_id
            FROM {$wpdb->get_blog_prefix()}posts p
                INNER JOIN {$wpdb->get_blog_prefix()}term_relationships tr ON tr.object_id = p.ID
                INNER JOIN {$wpdb->get_blog_prefix()}term_taxonomy tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                INNER JOIN {$wpdb->get_blog_prefix()}terms t ON t.term_id = tt.term_id
            WHERE p.post_type = 'PRODUCT' AND p.ID = $id_product AND tt.taxonomy = '$this->taxonomy_name'
        "
		);
	}

	/**
	 * Obtain term_taxonomy_id to generate extra select, join and where for posts and attribute
	 * queries, for following taxonomies:
	 * - Exclude from search
	 * - Variable product
	 *
	 * Also we replace slugs "-" to "_" in order to be usable as object and sql alias.
	 * Returned select statement will start with "," if variable product found. Empty otherwise.
	 * Returned where statement will start with "AND" if exclude from search found. Empty otherwise.
	 */
	public static function get_relevant_taxonomies() {
		if ( null !== static::$cache_relevant_taxonomies ) {
			return static::$cache_relevant_taxonomies;
		}

		global $wpdb;
		$taxonomies = $wpdb->get_results(
			"
      SELECT t.slug as slug, tt.term_taxonomy_id as term_taxonomy_id
      FROM {$wpdb->get_blog_prefix()}terms t
        INNER JOIN {$wpdb->get_blog_prefix()}term_taxonomy tt ON tt.term_id = t.term_id 
      WHERE (t.slug='variable' AND tt.taxonomy = 'product_type') OR (t.slug='exclude-from-search' AND tt.taxonomy = 'product_visibility')
      "
		);

		$taxonomy_select = '';
		$taxonomy_join   = array();
		$taxonomy_where  = '';
		foreach ( $taxonomies as $taxonomy ) {
			$alias           = str_replace( '-', '_', $taxonomy->slug );
			$taxonomy_join[] = "
        LEFT JOIN {$wpdb->get_blog_prefix()}term_relationships AS $alias
          ON (
            $alias.term_taxonomy_id = $taxonomy->term_taxonomy_id
            AND $alias.object_id = p.ID
          )";

			if ( 'exclude_from_search' === $alias ) {
				$taxonomy_where = 'AND exclude_from_search.object_id IS NULL';
			} elseif ( 'variable' === $alias ) {
				$taxonomy_select = ', COALESCE( variable.object_id, 0) AS p_is_variable';
			}
		}

		static::$cache_relevant_taxonomies = (object) array(
			'select_statement' => $taxonomy_select,
			'join_statement'   => implode( "\n", $taxonomy_join ),
			'where_statement'  => $taxonomy_where,
		);
		return static::$cache_relevant_taxonomies;
	}
}
