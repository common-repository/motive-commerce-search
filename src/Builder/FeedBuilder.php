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

use Motive\Woocommerce\Model\Feed;
use Motive\Woocommerce\Config;
use Motive\Woocommerce\LanguageManager;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * Class FeedBuilder
 * @package Motive\Woocommerce\Builder
 */
class FeedBuilder {

	protected $language_manager;
	protected $currency;

	/**
	 * SchemaBuilder constructor.
	 * @param LanguageManager $language_manager
	 * @param string $currency
	 */
	public function __construct( $language_manager, $currency ) {
		$this->language_manager = $language_manager;
		$this->currency         = $currency;
	}

	/**
	 * Feed constructor.
	 * @param int $from_id_product
	 * @return Feed
	 */
	public function build( $from_id_product = 0 ) {
		return Feed::build(
			MetadataBuilder::build( $this->language_manager, $this->currency ),
			$this->get_product_builder()->fetch_products_for( $from_id_product )
		);
	}

	/**
	 * @return ProductBuilder
	 * @throws Exception
	 */
	protected function get_product_builder() {
		$product_builder = Config::get_product_builder();
		return new $product_builder( $this->language_manager, $this->currency );
	}
}
