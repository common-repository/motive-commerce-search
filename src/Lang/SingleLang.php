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

namespace Motive\Woocommerce\Lang;

use Motive\Woocommerce\Builder\CurrencyBuilder;
use Motive\Woocommerce\Builder\LanguageBuilder;
use Motive\Woocommerce\Model\CatalogInfo;

class SingleLang implements LangInterface {

	public function get_default_language() {
		return $this->get_wp_language_code();
	}
	public function get_current_language() {
		return $this->get_wp_language_code();
	}
	public function get_language_url( $lang, $name ) {
		return get_rest_url( null, "motive/$lang/$name" );
	}
	public function get_language_list() {
		return array( $this->get_wp_language_code() );
	}
	public function get_inner_join_products_lang( $lang ) {
		return '';
	}
	public function get_label( $id, $product_attribute, $lang ) {
		if ( $product_attribute['is_taxonomy'] ) {
			return wc_attribute_label( $id );
		} else {
			return $product_attribute['name'];
		}
	}

	public function get_thumbnail_id( $object_id ) {
		return '';
	}

	public function get_product_image_gallery( $object_id ) {
		return '';
	}

	private function get_wp_language_code() {
		return explode( '-', get_bloginfo( 'language' ) )[0];
	}

	public function get_language_list_with_locale() {
		$lang_code = $this->get_wp_language_code();

		$catalog_info           = new CatalogInfo();
		$catalog_info->id       = 1;
		$catalog_info->code     = $lang_code;
		$catalog_info->locale   = str_replace( '_', '-', get_bloginfo( 'language' ) );
		$catalog_info->name     = LanguageBuilder::get_language_name_by_code( $catalog_info->code );
		$catalog_info->currency = CurrencyBuilder::from_code( get_woocommerce_currency() );
		return array( $lang_code => $catalog_info );
	}
}
