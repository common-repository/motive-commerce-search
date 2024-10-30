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

namespace Motive\Woocommerce\Model;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}


class Price {

	// Minimum difference between regular and sale prices to consider product is in sale
	const EPSILON = 0.005;

	public $regular;
	public $on_sale;

	public static function build( $regular, $on_sale = null ) {
		$price = new Price();

		$price->regular = floatval( $regular );
		$price->on_sale = null;

		if ( $on_sale !== null ) {
			$on_sale = floatval( $on_sale );
			if ( $on_sale > 0 && abs( $price->regular - $on_sale ) > self::EPSILON ) {
				$price->on_sale = $on_sale;
			}
		}

		return $price;
	}
}
