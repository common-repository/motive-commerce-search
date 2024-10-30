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
use Motive\Woocommerce\Model\CatalogInfo;

class WpmlLang implements LangInterface {
	private $local_attribute_translations = null;

	public function get_default_language() {
		global $sitepress;
		return $sitepress->get_default_language();
	}
	public function get_current_language() {
		return apply_filters( 'wpml_current_language', null );
	}
	public function get_language_url( $lang, $name ) {
		return apply_filters( 'wpml_permalink', get_rest_url( null, "motive/$lang/$name" ), $lang );
	}
	public function get_language_list() {
		$active_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
		return array_keys( $active_languages );
	}
	public function get_inner_join_products_lang( $lang ) {
		global $wpdb;
		$inner_join = "INNER JOIN {$wpdb->get_blog_prefix()}icl_translations AS t
                    ON (p.ID=t.element_id AND t.element_type='post_product' AND t.language_code='$lang')";
		return $inner_join;
	}

	public function get_label( $id, $product_attribute, $lang ) {
		if ( $lang === $this->get_default_language() ) {
			if ( $product_attribute['is_taxonomy'] ) {
				return wc_attribute_label( $id );
			} else {
				return $product_attribute['name'];
			}
		}
		if ( $product_attribute['is_taxonomy'] ) {
			return $this->get_translated_taxonomy_label( wc_attribute_label( $id ), $lang );
		} else {
			return $this->get_translated_local_label( $id, $lang );
		}
	}

	public function get_thumbnail_id( $object_id ) {
		return get_post_meta( $object_id, '_thumbnail_id', true );
	}

	public function get_product_image_gallery( $object_id ) {
		return get_post_meta( $object_id, '_product_image_gallery', true );
	}

	private function get_translated_local_label( $label, $lang ) {
		if ( null === $this->local_attribute_translations ) {
			$this->local_attribute_translations = $this->cache_local_translations( $lang );
		}
		return $this->local_attribute_translations[ $label ];
	}

	private function cache_local_translations( $lang ) {

		global $wpdb;
		$prefix             = $wpdb->get_blog_prefix();
		$label_translations = $wpdb->get_results(
			"
            SELECT pm.meta_value
            FROM {$prefix}postmeta pm
            WHERE pm.meta_key = 'attr_label_translations'
        "
		);

		$local_attribute_translations = array();
		foreach ( $label_translations as $label_translation ) {
			$label_translation = maybe_unserialize( $label_translation->meta_value );
			if ( ! is_array( $label_translation ) || empty( $label_translation[ $lang ] ) || ! is_array( $label_translation[ $lang ] ) ) {
				continue;
			}
			foreach ( $label_translation[ $lang ] as $label => $translation ) {
				$local_attribute_translations[ $label ] = $translation;
			}
		}
		return $local_attribute_translations;
	}

	private function get_translated_taxonomy_label( $label, $lang ) {
		global $wpdb;
		$prefix           = $wpdb->get_blog_prefix();
		$translated_label = $wpdb->get_var(
			"
            SELECT st.value AS translated
            FROM {$prefix}icl_strings s
            LEFT JOIN {$prefix}icl_string_translations st ON s.ID = st.string_id
            WHERE s.value = '$label' AND st.language='$lang'
        "
		);
		if ( ! $translated_label ) {
			$translated_label = $label;
		}
		return $translated_label;
	}

	public function get_language_list_with_locale() {
		$active_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
		$language_list    = array();

		foreach ( $active_languages as $code => $language ) {
			$catalog_info = new CatalogInfo();

			$catalog_info->id       = $language['id'];
			$catalog_info->code     = $code;
			$catalog_info->locale   = str_replace( '_', '-', $language['default_locale'] );
			$catalog_info->name     = $language['translated_name'];
			$catalog_info->currency = CurrencyBuilder::from_code( get_woocommerce_currency() );
			$language_list[ $code ] = $catalog_info;
		}

		return $language_list;
	}
}
