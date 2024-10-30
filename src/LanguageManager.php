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

namespace Motive\Woocommerce;

use Motive\Woocommerce\Lang\LangInterface;
use Motive\Woocommerce\Lang\SingleLang;
use Motive\Woocommerce\Lang\WpmlLang;

class LanguageManager {
	/** @var string $default_lang */
	public $default_lang;

	/** @var string $current_lang */
	public $current_lang;

	/** @var string[] $langs */
	public $langs;

	/** @var LangInterface $handler */
	private $handler = null;

	private static $instance = null;

	public static function get_instance( $current_lang = null, $handler = null ) {
		if ( null === static::$instance ) {
			static::$instance = new LanguageManager( $current_lang, $handler );
		}
		return static::$instance;
	}

	public function __construct( $current_lang = null, $handler = null ) {
		$this->handler = $handler;
		if ( null === $handler ) {
			$this->handler = $this->decide_lang_handler();
		}
		$this->set_current_lang( $current_lang );
		$this->default_lang = $this->handler->get_default_language();
		$this->langs        = $this->handler->get_language_list();
	}

	public function set_current_lang( $current_lang ) {
		$this->current_lang = $current_lang;
		if ( null === $current_lang ) {
			$this->current_lang = $this->handler->get_current_language();
		}
	}

	public function get_language_list_with_locale() {
		return $this->handler->get_language_list_with_locale();
	}

	private function decide_lang_handler() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		global $sitepress;
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) && $sitepress ) {
			return new WpmlLang();
		}
		return new SingleLang();
	}

	public function get_inner_join_products_lang() {
		return $this->handler->get_inner_join_products_lang( $this->current_lang );
	}

	public function get_label( $id, $product_attribute ) {
		return html_entity_decode( $this->handler->get_label( $id, $product_attribute, $this->current_lang ) );
	}

	public function load_schemabuilder_textdomain() {
		$two_chars_lang = explode( '-', $this->current_lang )[0];
		load_textdomain( 'schemabuilder', plugin_dir_path( __FILE__ ) . "../languages/schemabuilder-$two_chars_lang.mo" );
	}

	public function load_motive_textdomain() {
		$two_chars_lang = explode( '-', $this->current_lang )[0];
		load_textdomain( 'motive-commerce-search', plugin_dir_path( __FILE__ ) . "../languages/motive-commerce-search-$two_chars_lang.mo" );
	}

	public function get_language_url( $lang, $name ) {
		return $this->handler->get_language_url( $lang, $name );
	}

	public function get_thumbnail_id( $object_id ) {
		return $this->handler->get_thumbnail_id( $object_id );
	}

	public function get_product_image_gallery( $object_id ) {
		return $this->handler->get_product_image_gallery( $object_id );
	}
}
