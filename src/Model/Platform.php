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
 * Optional properties:
 * @property bool memory_limit_allow_change
 * @property string memory_limit
 * @property bool max_execution_time_allow_change
 * @property string max_execution_time
 */
#[\AllowDynamicProperties]
class Platform {

	/** @var string Platform Name */
	public $name;

	/** @var string */
	public $version;

	/**
	 * Platform info builder.
	 * @return static
	 */
	public static function build() {
		$platform                                  = new static();
		$platform->name                            = 'PHP';
		$platform->version                         = PHP_VERSION;
		$platform->max_execution_time              = ini_get( 'max_execution_time' );
		$platform->max_execution_time_allow_change = set_time_limit( 20 );
		$platform->memory_limit                    = ini_get( 'memory_limit' );
		$platform->memory_limit_allow_change       = $platform->canChangeInitVar( 'memory_limit', '234M' );
		return $platform;
	}

	/**
	 * @param string $varname
	 * @param string $value
	 * @return bool
	 */
	protected function canChangeInitVar( $varname, $value ) {
		$before = ini_get( $varname );
		ini_set( $varname, $value );
		$after = ini_get( $varname );

		return $before !== $after;
	}
}
