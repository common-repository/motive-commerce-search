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

use Motive\Woocommerce\Model\Category;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class CategoryBuilder extends TaxonomyBuilder {

	public function __construct() {
		parent::__construct( 'product_cat' );
	}

	/**
	 * Returns the product's categories path
	 *
	 * @param int $id_product
	 *
	 * @return string[] array of categories path
	 */
	public function fetch_for( $id_product ) {
		$product_categories = array();
		foreach ( $this->fetch_for_product( $id_product ) as $product_category ) {
			$path = $this->get_path( $product_category->term_id );
			if ( '' !== $path ) {
				$product_categories[] = $path;
			}
		}

		return $product_categories;
	}

	/**
	 * Return category path as string
	 * @param $category_id
	 * @return string
	 */
	protected function get_path( $category_id ) {
		// The category no longer exists, but it still has products associated with it.
		if ( empty( $this->taxonomies[ $category_id ] ) ) {
			return '';
		}

		$category = &$this->taxonomies[ $category_id ];
		if ( empty( $category->path ) ) {
			$category->path = html_entity_decode( $category->term_id ) . Category::SEPARATOR . html_entity_decode( $category->name );
			if ( ! empty( $category->parent ) ) {
				$parent_path    = $this->get_path( $category->parent );
				$category->path = html_entity_decode( $parent_path ) . Category::TREE_SEPARATOR . html_entity_decode( $category->path );
			}
		}

		return $category->path;
	}
}
