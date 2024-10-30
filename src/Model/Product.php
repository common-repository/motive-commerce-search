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

/**
 * Class Product
 * @property Variation[] variation Array of product's variations
 * @package Motive\Woocommerce\Model
 */
#[\AllowDynamicProperties]
class Product {

	// basic product data
	/** @var int Product's unique identifier */
	public $id;
	/** @var string Product's visible name */
	public $name;
	/** @var string Product's description */
	public $description;
	/** @var string Product's short description */
	public $short_description;
	/** @var string Product's landing page */
	public $url;
	/** @var Image[] Product's images */
	public $images;

	// price & availability
	/** @var Availability */
	public $availability;
	/** @var Price */
	public $price;

	// product category
	/** @var string[] Array of product's categories */
	public $category;

	/** @var string Product's identifier */
	public $sku;

    // product labels
    /** @var ProductLabel[] */
    public $labels;
}
