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

class TagsBuilder extends TaxonomyBuilder {

	public function __construct() {
		parent::__construct( 'product_tag' );
	}

	/**
	 * Returns tags assigned to given product id separated by ,
	 *
	 * @param int $id_product
	 *
	 * @return Array Product's tags ids separated by ,
	 */
	public function fetch_for( $id_product ) {
		$tags = array();
		foreach ( $this->fetch_for_product( $id_product ) as $taxonomy ) {
			$tags[] = $this->taxonomies[ $taxonomy->term_id ]->name;
		}
		return $tags;
	}
}
