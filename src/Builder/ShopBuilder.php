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
use Motive\Woocommerce\Model\Shop;
use Motive\Woocommerce\LanguageManager;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class ShopBuilder {

	/**
	 * Shop builder from Prestashop Shop ID and Name.
	 * @param LanguageManager $lang_manager
	 * @param int $id_shop
	 * @param string $name
	 * @return Shop
	 */
	public static function from_id_and_name( $lang_manager, $id_shop, $name ) {
		$shop             = new Shop();
		$shop->id         = $id_shop;
		$shop->name       = $name;
		$shop->url        = self::get_shop_url();
		$shop->languages  = $lang_manager->langs;
		$shop->currencies = static::get_shop_active_currencies( $id_shop );
		return $shop;
	}

	/**
	 * Returns the shop url
	 *
	 * @return string
	 */
	public static function get_shop_url() {
		return get_home_url();
	}

	/**
	 * Returns the available active currencies for the selected shop
	 *
	 * @return Currency[] array of active currencies
	 */
	protected static function get_shop_active_currencies() {
		return array( CurrencyBuilder::from_code( get_woocommerce_currency() ) );
	}
}
