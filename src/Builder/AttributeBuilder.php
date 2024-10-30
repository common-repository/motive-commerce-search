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

use Motive\Woocommerce\Config;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

abstract class AttributeBuilder {

	protected $language_manager;

	public $attributes = array();
	public $features   = array();
	private $features_limit;

	public function __construct( $language_manager ) {
		$this->language_manager = $language_manager;
		$this->features_limit   = intval( Config::get_features_limit() );
	}

	/**
	 * Fetch all attributes & features into local public vars
	 */
	abstract public function fetch();

	protected function finalize_attributes_retrieval( $attributes, $features, $feature_counter ) {
		if ( $this->features_limit > 0 ) {
			arsort( $feature_counter, SORT_NUMERIC );
			$top_used_features = array_slice( $feature_counter, 0, $this->features_limit );
			$features          = array_intersect_key( $features, $top_used_features );
			Config::set_top_used_features( array( $this->language_manager->current_lang => $top_used_features ) );
		}

		$this->attributes = array_values( $attributes );
		$this->features   = array_values( $features );
	}

	/**
	 * Creates an unique identifier for features
	 *
	 * @param string $id entity id
	 * @return string
	 */
	public static function get_feature_key( $id ) {
		if ( Config::get_ff_unprefix_fields() ) {
			return $id;
		}
		return "f$id";
	}


	/**
	 * Creates an unique identifier for attributes
	 *
	 * @param string $id entity id
	 * @return string
	 */
	public static function get_attribute_key( $id ) {
		if ( Config::get_ff_unprefix_fields() ) {
			return $id;
		}
		return "a$id";
	}
}
