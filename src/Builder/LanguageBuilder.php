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

use Motive\Woocommerce\Model\Language;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class LanguageBuilder {
	/**
	 * Language builder from iso639 code.
	 * @param string $code
	 * @return Language
	 */
	public static function from_code( $code ) {
		$language           = new Language();
		$language->name     = self::get_language_name_by_code( $code );
		$language->iso_code = $code;
		return $language;
	}

	public static function get_language_name_by_code( $code ) {
		if ( function_exists( 'locale_get_display_language' ) ) {
			return locale_get_display_language( $code );
		}
		return $code;
	}
}
