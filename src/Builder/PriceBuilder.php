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

use Motive\Woocommerce\Model\Price;
use Motive\Woocommerce\MotiveDateTools;

class PriceBuilder {

	public $taxes_enabled                     = false;
	private $prices_created_with_tax_included = false;
	private $prices_shown_with_tax_in_shop    = false;

	private $rates = array();

	/**
	 * PriceBuilder constructor.
	 */
	public function __construct() {
		if ( get_option( 'woocommerce_calc_taxes' ) !== 'yes' ) {
			return;
		}
		$this->prices_created_with_tax_included = get_option( 'woocommerce_prices_include_tax' ) === 'yes';
		$this->prices_shown_with_tax_in_shop    = get_option( 'woocommerce_tax_display_shop' ) === 'incl';
		$this->taxes_enabled                    = $this->prices_created_with_tax_included !== $this->prices_shown_with_tax_in_shop;

		add_filter( 'woocommerce_get_tax_location', array( $this, 'set_shop_location_if_unavailable' ) );
	}

	/**
	 * If no location is available (which happens on geolocation), we will set the shop base address
	 * for tax calculations.
	 *
	 * @param object $location - Location obtainer from woocommerce.
	 * @return object
	 */
	public function set_shop_location_if_unavailable( $location ) {
		if ( empty( $location ) ) {
			$location = array(
				WC()->countries->get_base_country(),
				WC()->countries->get_base_state(),
				WC()->countries->get_base_postcode(),
				WC()->countries->get_base_city(),
			);
		}
		return $location;
	}

	/**
	 * Return price object from product, taking in care taxes
	 *
	 * @param object $row - Product row
	 * @return Price
	 */
	public function get_product_price( $row ) {
		return $this->get_price(
			$row->p_regular_price,
			$row->p_sale_price,
			$row->p_sale_price_dates_from,
			$row->p_sale_price_dates_to,
			'taxable' === $row->p_tax_status ? $row->p_tax_class : null
		);
	}

	/**
	 * Return price object for variation, taking in care taxes
	 *
	 * @param object $product_row - Product row
	 * @param object $variation_row - Variation row
	 * @return Price
	 */
	public function get_variation_price( $variation_row, $product_row ) {
		$tax_class = 'parent' === $variation_row->v_tax_class ? $product_row->p_tax_class : $variation_row->v_tax_class;
		return $this->get_price(
			$variation_row->v_regular_price,
			$variation_row->v_sale_price,
			$variation_row->v_sale_price_dates_from,
			$variation_row->v_sale_price_dates_to,
			'taxable' === $product_row->p_tax_status ? $tax_class : null
		);
	}

	/**
	 * Return price object given a price, sale_price, sale dates and its tax class.
	 *
	 * @param float $price - Product price.
	 * @param float $sale_price - Product sale price.
	 * @param string $start_sale
	 * @param string $end_sale
	 * @param string $tax_class - class of taxes to apply or null if its not taxable.
	 * @return Price
	 */
	protected function get_price( $price, $sale_price, $start_sale, $end_sale, $tax_class ) {
		if ( $start_sale || $end_sale ) {
			$sale_price = MotiveDateTools::is_today_between( $start_sale, $end_sale ) ? $sale_price : null;
		}
		if ( $this->taxes_enabled && null !== $tax_class ) {
			if ( ! isset( $this->rates[ $tax_class ] ) ) {
				// https://woocommerce.github.io/code-reference/classes/WC-Tax.html#method_get_rates
				$this->rates[ $tax_class ] = \WC_Tax::get_rates( $tax_class );
			}
			$rates      = $this->rates[ $tax_class ];
			$price      = $this->apply_tax( $price, $rates );
			$sale_price = ! empty( $sale_price ) ? $this->apply_tax( $sale_price, $rates ) : null;
		}
		return Price::build( $price, $sale_price );
	}

	/**
	 * From a price and its tax rates, we calculate here the price with taxes.
	 * Only should do something when:
	 * 1- User adds prices WITH taxes, but shows it WITHOUT taxes. We should subtract taxes here.
	 * 2- User adds prices WITHOUT taxes, but shows it WITH taxes. Adding then.
	 *
	 * @param float $price - price to add/subtract taxes.
	 * @param array $rates - class of taxes to apply.
	 * @return float
	 */
	protected function apply_tax( $price, $rates ) {
		$price = (float) $price;
		// https://woocommerce.github.io/code-reference/classes/WC-Tax.html#method_calc_tax
		$tax = array_sum( \WC_Tax::calc_tax( $price, $rates, $this->prices_created_with_tax_included ) );
		return $this->prices_shown_with_tax_in_shop ? $price + $tax : $price - $tax;
	}

	/**
	 * Returns an object with the min and max rates which applies in current context. Current context
	 * may be while indexation is being done (to store the rates which applied in indexation time), or
	 * can be made from a user request, to know which rates should apply to the user.
	 *
	 * @return array
	 */
	public function get_tax_rate_range() {
		if ( ! $this->taxes_enabled ) {
			return array(
				'min' => 1,
				'max' => 1,
			);
		}
		$min = 100;
		$max = 0;

		$tax_classes   = \WC_Tax::get_tax_class_slugs();
		$tax_classes[] = '';
		foreach ( $tax_classes as $tax_class ) {
			$wc_rate = \WC_Tax::get_rates( $tax_class );
			$rate    = $this->apply_tax( 1, $wc_rate );
			if ( $rate > $max ) {
				$max = $rate;
			}

			if ( $rate < $min ) {
				$min = $rate;
			}
		}

		return array(
			'min' => $min,
			'max' => $max,
		);
	}
}
