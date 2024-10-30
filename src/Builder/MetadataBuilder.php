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

use DateTime;
use Motive\Woocommerce\LanguageManager;
use Motive\Woocommerce\Model\Metadata;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class MetadataBuilder {

	/**
	 * Returns a MotiveMetadata object based on the selected shop & lang
	 *
	 * @param LanguageManager $language_manager
	 * @param string $currency
	 *
	 * @return Metadata
	 */
	public static function build( $language_manager, $currency ) {
		$metadata             = new Metadata();
		$metadata->lang       = $language_manager->current_lang;
		$metadata->shop       = get_permalink( wc_get_page_id( 'shop' ) );
		$metadata->currency   = $currency;
		$metadata->created_at = ( new DateTime() )->format( DateTime::ATOM );
		$metadata->source     = self::get_source();

		return $metadata;
	}

	public static function get_source() {
		global $wp_version;
		return 'Motive/' . MOTIVE_VERSION . '; WooCommerce/' . WC_VERSION . '; WordPress/' . $wp_version . '; PHP/' . PHP_VERSION;
	}
}
