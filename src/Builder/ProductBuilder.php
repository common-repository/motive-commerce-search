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

use Motive\Woocommerce\Model\Product;
use Motive\Woocommerce\MotiveStrTools;
use Motive\Woocommerce\Config;
use Exception;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * Class ProductBuilder
 * @package Motive\Woocommerce\Builder
 */
abstract class ProductBuilder {
	const POSTMETA_KEYS = array(
		'_virtual',
		'_sku',
		'_stock_status',
		'_stock',
		'_manage_stock',
		'_regular_price',
		'_sale_price',
		'_sale_price_dates_from',
		'_sale_price_dates_to',
		'_children',
		'_tax_status',
		'_tax_class',
		'_default_attributes',
		'_product_attributes',
		'_product_image_gallery',
		'_thumbnail_id',
	);
	protected $currency;
	protected $label_builder;
	protected $language_manager;
	protected $availability_builder;
	protected $category_builder;
	protected $tags_builder;
	protected $image_builder;
	protected $price_builder;
	protected $variation_builder;
	protected $top_used_features = null;
	protected $product_batch_size;

	/**
	 * This function should return array of $rows with all needed info inside.
	 * @param int $from_id_product
	 * @return Traversable<array> rows
	 */
	abstract protected function query_products( $from_id_product = 0 );

	/**
	 * ProductBuilder constructor.
	 * @param string $currency Currency used to fetch products
	 */
	public function __construct( $language_manager, $currency ) {
		$this->language_manager     = $language_manager;
		$this->currency             = $currency;
		$this->availability_builder = new AvailabilityBuilder();
		$this->category_builder     = new CategoryBuilder();
		$this->tags_builder         = new TagsBuilder();
		$this->image_builder        = new ImageBuilder();
		$this->price_builder        = new PriceBuilder();
		$this->variation_builder    = new VariationBuilder( $this->image_builder, $this->price_builder, $this->language_manager );
		$this->label_builder        = new ProductLabelBuilder();

		if ( intval( Config::get_features_limit() ) > 0 ) {
			$top_used_features = Config::get_top_used_features();
			if ( empty( $top_used_features[ $this->language_manager->current_lang ] ) ) {
				$attribute_builder_class = Config::get_attribute_builder();
				$attribute_builder       = new $attribute_builder_class( $this->language_manager );
				$attribute_builder->fetch();
				$feature_ids             = array_column( $attribute_builder->features, 'id' );
				$this->top_used_features = array_combine( $feature_ids, $feature_ids );
			} else {
				$this->top_used_features = $top_used_features[ $this->language_manager->current_lang ];
			}
		}

		$this->store_price_rates();

		$this->product_batch_size = max( (int) Config::get_product_batch_size(), 2 );
	}

	/**
	 * Returns the products for the selected shop & lang
	 * @param int $from_id_product
	 * @return Traversable<Product>|[] array of products
	 */
	public function fetch_products_for( $from_id_product = 0 ) {
		foreach ( $this->query_products( $from_id_product ) as $row ) {
			try {
				$product = $this->from_row( $row );
			} catch ( Exception $e ) {
				continue;
			}

			// If product is not variable, we don't try to obtain its variations, as there are cases
			// in which some garbage data about variations is in db.
			if ( 0 === (int) $row->p_is_variable ) {
				yield $product;
				continue;
			}

			try {
				$variants = $this->variation_builder->fetch_for( $product->id, $row );
			} catch ( Exception $e ) {
				continue;
			}

			if ( empty( $variants ) ) {
				yield $product;
				continue;
			}

			$product->variation = $variants;

			/**
			 * If we don't have price for product, it will be show as out of stock in MCS.
			 * In woocommerce, parent product of variations doesn't have price, so we add this
			 * to set a price for parent product from the default variant
			 */
			foreach ( $variants as $variant ) {
				if ( $variant->is_default ) {
					$product->availability = $variant->availability;
					$product->price        = $variant->price;
					$product->on_sale      = $variant->on_sale;
					break;
				}
			}

			yield $product;
		}
	}

	/**
	 * @param object $row
	 * @return Product
	 */
	protected function from_row( $row ) {
		$product                    = new Product();
		$product->id                = $row->p_id;
		$product->name              = MotiveStrTools::clean_string( $row->p_name );
		$product->description       = MotiveStrTools::clean_string( $row->p_desc );
		$product->short_description = MotiveStrTools::clean_string( $row->p_short_desc );
		$product->url               = get_permalink( $product->id );

		$product->images       = $this->get_images_from_row( $row );
		$product->availability = $this->availability_builder->build_from(
			(int) $row->p_stock,
			(string) $row->p_manage_stock,
			(string) $row->p_stock_status
		);
		$product->category     = $this->category_builder->fetch_for( $product->id );
		$product->tags         = $this->tags_builder->fetch_for( $product->id );
		$product->sku          = $row->p_sku;

		$product->is_bundle  = (bool) $row->p_children;
		$product->is_virtual = 'yes' === $row->p_virtual;

		$product->price   = $this->price_builder->get_product_price( $row );
		$product->on_sale = ! empty( $product->price->on_sale ) && $product->price->regular !== $product->price->on_sale;

		// Products features
		$features = FeatureValueBuilder::fetch_for_product( $row->p_id, $row->p_product_attributes, 0 === (int) $row->p_is_variable );
		foreach ( $features as $feature ) {
			if ( null !== $this->top_used_features && ! isset( $this->top_used_features[ $feature->key ] ) ) {
				continue;
			}

			$field_name           = $feature->key;
			$product->$field_name = $feature->values;
		}

		$product->labels = $this->label_builder->get_for_product( $product );
		$product         = apply_filters( 'motive_feed_product', $product );
		return $product;
	}

	/**
	 * Utility function to get image related ids and provide to image builder.
	 */
	private function get_images_from_row( $row ) {
		$attachment_ids = array();

		$thumbnail_id = ! empty( $row->p_thumbnail_id ) ?
			$row->p_thumbnail_id :
			$this->language_manager->get_thumbnail_id( $row->p_id );

		if ( ! empty( $thumbnail_id ) ) {
			$attachment_ids[] = $thumbnail_id;
		}

		$gallery_ids = ! empty( $row->p_product_image_gallery ) ?
			$row->p_product_image_gallery :
			$this->language_manager->get_product_image_gallery( $row->p_id );

		if ( ! empty( $gallery_ids ) ) {
			// In some cases, wp stores image list with a trailing comma, so we trim it before exploding.
			foreach ( explode( ',', trim( $gallery_ids, ", \n\r\t\v\x00" ) ) as $gallery_id ) {
				$attachment_ids[] = $gallery_id;
			}
		}

		return $this->image_builder->get_from_attachment_ids( $attachment_ids );
	}

	/**
	 * Storing price rates on indexation time, to be able to operate with front client rates.
	 */
	private function store_price_rates() {
		if ( empty( $_SERVER['HTTP_USER_AGENT'] ) || false === stripos( $_SERVER['HTTP_USER_AGENT'], 'motive' ) ) {
			return;
		}

		$tax_range = wp_json_encode( $this->price_builder->get_tax_rate_range() );
		Config::set_last_sync_info( array( $this->language_manager->current_lang => $tax_range ) );
	}
}
