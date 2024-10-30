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
 * @property Field[] fields
 * @property string label
 * @property array features
 */
#[\AllowDynamicProperties]
class Field {

	public $id;
	public $type;
	public $path;

	/**
	 * MotiveField constructor.
	 * @param string $id
	 * @param string $type
	 * @param string $path
	 */
	public function __construct( $id, $type, $path ) {
		$this->id   = $id;
		$this->type = $type;
		$this->path = $path;
	}

	/**
	 * Static constructor
	 * @param $id
	 * @param $type
	 * @param $path
	 * @return Field
	 */
	public static function build( $id, $type, $path = null ) {
		return new Field( $id, $type, $path !== null ? $path : $id );
	}

	/**
	 * Set Searchable feature, default true
	 * @param bool $active
	 * @return $this
	 */
	public function setSearchable( $active = true ) {
		$this->features['searchable'] = $active;
		return $this;
	}

	/**
	 * Set Facetable feature, default true
	 * @param bool $active
	 * @return $this
	 */
	public function setFacetable( $active = true ) {
		$this->features['facetable'] = $active;
		return $this;
	}

	/**
	 * Set Sortable feature, default true
	 * @param bool $active
	 * @return $this
	 */
	public function setSortable( $active = true ) {
		$this->features['sortable'] = $active;
		return $this;
	}

    /**
     * Set Retrievable feature, default true
     * @param bool $active
     * @return $this
     */
    public function setRetrievable($active = true) {
        $this->features['retrievable'] = $active;
        return $this;
    }

	/**
	 * Set features array
	 * @param array $features
	 * @return $this
	 */
	public function setFeatures( array $features ) {
		$this->features = $features;
		return $this;
	}

	/**
	 * Set SubFields array
	 * @param array $fields
	 * @return $this
	 */
	public function setFields( array $fields ) {
		$this->fields = $fields;
		return $this;
	}

	/**
	 * @param string $label
	 * @return $this
	 */
	public function setLabel( $label ) {
		$this->label = $label;
		return $this;
	}
}
