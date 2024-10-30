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

use Motive\Woocommerce\Model\Currency;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class CurrencyBuilder {
	protected static $currency_names = null;
	/**
	 * Currency builder from code.
	 * @param string $currency_code
	 * @return Currency
	 */
	public static function from_code( $currency_code ) {
		if ( null === static::$currency_names ) {
			static::$currency_names = get_woocommerce_currencies();
		}
		$currency           = new Currency();
		$currency->name     = static::$currency_names[ $currency_code ];
		$currency->iso_code = $currency_code;
		$currency->symbol   = html_entity_decode( get_woocommerce_currency_symbol( $currency_code ) );
		return $currency;
	}
}
