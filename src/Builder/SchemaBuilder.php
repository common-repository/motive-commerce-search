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

use Motive\Woocommerce\Model\Field;
use Motive\Woocommerce\Model\FieldType;
use Motive\Woocommerce\Model\Schema;
use Motive\Woocommerce\Config;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * Class SchemaBuilder
 * @package Motive\Woocommerce\Builder
 */
class SchemaBuilder {

	/** @var \Motive\Woocommerce\LanguageManager */
	protected $language_manager;

	/** @var string */
	protected $currency;

	/**
	 * SchemaBuilder constructor.
	 * @param \Motive\Woocommerce\LanguageManager $language_manager
	 * @param string $currency
	 */
	public function __construct( $language_manager, $currency ) {
		$this->language_manager = $language_manager;
		$this->currency         = $currency;
	}

	/**
	 * Build a feed schema for the current shop, lang & currency
	 * @return Schema
	 */
	public function build() {
		$schema_product_label_builder = new SchemaProductLabelBuilder();
		return apply_filters(
			'motive_schema',
			Schema::build(
				MetadataBuilder::build( $this->language_manager, $this->currency ),
				$this->build_fields(),
				$schema_product_label_builder->get_available_labels()
			)
		);
	}

	/**
	 * Build a feed schema fields array
	 *
	 * @return Field[]
	 */
	public function build_fields() {
		$this->language_manager->load_schemabuilder_textdomain();

		$fields   = $this->build_shared_fields();
		$fields[] = Field::build( 'name', FieldType::NAME )
			->setLabel( __( 'Name', 'schemabuilder' ) )
			->setSearchable();
		$fields[] = Field::build( 'short_description', FieldType::TEXT )
			->setLabel( __( 'Short description', 'schemabuilder' ) )
			->setSearchable();
		$fields[] = Field::build( 'category', FieldType::CATEGORY )
			->setLabel( __( 'Category', 'schemabuilder' ) )
			->setSearchable()
			->setFacetable();

		// Additional searchable fields
		$fields[] = Field::build( 'tags', FieldType::TEXT )
			->setLabel( __( 'Tags', 'schemabuilder' ) )
			->setSearchable()
			->setFacetable();

			$attribute_builder_class = Config::get_attribute_builder();
			$attribute_builder       = new $attribute_builder_class( $this->language_manager );
		$attribute_builder->fetch();

		// Products features (attributes not for variants)
		foreach ( $attribute_builder->features as $feature ) {
			$fields[] = Field::build( $feature->id, FieldType::ATTRIBUTE )
				->setLabel( $feature->name )
				->setSearchable()
				->setFacetable();
		}

		$fields[] = Field::build( 'is_bundle', FieldType::BOOLEAN )
			->setLabel( __( 'Bundle', 'schemabuilder' ) )
			->setFacetable();

		$fields[] = Field::build( 'is_virtual', FieldType::BOOLEAN )
			->setLabel( __( 'Virtual', 'schemabuilder' ) )
			->setFacetable();

		$fields[] = Field::build( 'is_featured', FieldType::BOOLEAN )
			->setLabel( __( 'Featured', 'schemabuilder' ) )
			->setFacetable();

		$fields[] = Field::build( 'on_sale', FieldType::BOOLEAN )
			->setLabel( __( 'On Sale', 'schemabuilder' ) )
			->setFacetable();

		// Products variations/attributes
		$var_fields = array();
		foreach ( $attribute_builder->attributes as $attribute ) {
			$type         = $attribute->isColor ? FieldType::COLOR : FieldType::ATTRIBUTE;
			$var_fields[] = Field::build( $attribute->id, $type )
				->setLabel( $attribute->name )
				->setSearchable()
				->setFacetable();
		}
		$var_fields[] = Field::build( 'is_default', FieldType::VARIATION_DEFAULT );
		$var_fields   = array_merge( $this->build_shared_fields( 'v_' ), $var_fields );
		$fields[]     = Field::build( 'variation', FieldType::VARIATION )->setFields( $var_fields );

		return $fields;
	}

	/**
	 * Build fields array for product and variation
	 *
	 * @return Field[]
	 */
	protected function build_shared_fields( $id_prefix = '' ) {
		return array(
			Field::build( $id_prefix . 'id', FieldType::ID, 'id' ),
			Field::build( $id_prefix . 'url', FieldType::LINK, 'url' ),
			Field::build( $id_prefix . 'description', FieldType::DESCRIPTION, 'description' )
				->setLabel( __( 'Description', 'schemabuilder' ) )
				->setSearchable(),
			Field::build( $id_prefix . 'images', FieldType::IMAGE, 'images' ),
			Field::build( $id_prefix . 'availability', FieldType::AVAILABILITY, 'availability' )
				->setLabel( __( 'In Stock', 'schemabuilder' ) )
				->setFacetable(),
			Field::build( $id_prefix . 'price', FieldType::PRICE, 'price' )
				->setLabel( __( 'Price', 'schemabuilder' ) )
				->setFacetable()
				->setSortable(),
			Field::build( $id_prefix . 'sku', FieldType::CODE_REFERENCE, 'sku' )
				->setLabel( __( 'SKU', 'schemabuilder' ) )
				->setSearchable(),
			Field::build( $id_prefix . 'labels', FieldType::PRODUCT_LABEL, 'labels' )
				->setLabel( __( 'Product labels', 'schemabuilder' ) )
				->setRetrievable(),
		);
	}
}
