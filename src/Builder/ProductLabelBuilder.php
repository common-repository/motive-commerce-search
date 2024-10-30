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

use Motive\Woocommerce\Model\Product;
use Motive\Woocommerce\Model\ProductLabel;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class ProductLabelBuilder {

	/**
	 * Returns the product's labels
	 *
	 * @param Product $product - a product
	 * @return ProductLabel[] array of tags
	 */
	public static function get_for_product( $product ) {
		$flags = array();
		if ( $product->on_sale ) {
			$flags[] = ProductLabel::build( 'on-sale' );
		}
		return $flags;
	}
}
