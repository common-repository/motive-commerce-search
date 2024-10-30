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

use ArrayObject;
use JsonSerializable;
use Traversable;

/**
 * Class Feed
 * @package Motive\Woocommerce\Model
 */
class Feed implements JsonSerializable {

	/** @var Metadata Feed's metadata */
	public $metadata;
	/** @var Product[]|Traversable<Product> Feed's products */
	public $products;

	/**
	 * Feed constructor.
	 * @param Metadata $metadata
	 * @param Product[]|Traversable<Product> $products
	 * @return Feed
	 */
	public static function build( Metadata $metadata, $products ) {
		$feed           = new static();
		$feed->metadata = $metadata;
		$feed->products = $products;
		return $feed;
	}

	/**
	 * Simple way of serialize and stream this.
	 * @param int $options [Optional] Bitmask consisting of JSON constants described on the JSON constants page.
	 * @param callable|null $abortCallBack return true / next url string if abort is required
	 */
	public function jsonStream( $options = 0, callable $abortCallBack = null ) {
		headers_sent() || header( 'Content-Type:application/json; charset=utf-8' );
		$prods = $this->products;
		if ( ! $prods instanceof Traversable ) {
			$prods = ( new ArrayObject( $prods ) )->getIterator();
		}
		$abortedOn = null;
		echo '{';
		echo '"metadata": ' . json_encode( $this->metadata, $options ) . ',';
		echo '"products": [';
		$prods->rewind();
		// First iteration is different: Cant be aborted and do not need comma separator.
		if ( $prods->valid() ) {
			$current = $prods->current();
			echo json_encode( $current, $options );
			$prods->next();
		}
		while ( $prods->valid() ) {
			// Here, $current is defined with previous current value
			if ( $abortCallBack !== null && ( $abortedOn = $abortCallBack( $current->id ) ) ) {
				break;
			}
			$current = $prods->current();
			echo ",\n" . json_encode( $current, $options );
			$prods->next();
		}
		echo ']';
		if ( is_string( $abortedOn ) ) {
			echo ',"next_page": ' . json_encode($abortedOn);
		}
		echo '}';
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return self data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize() {
		if ( ! is_array( $this->products ) && $this->products instanceof Traversable ) {
			$this->products = iterator_to_array( $this->products );
		}

		return $this;
	}
}
