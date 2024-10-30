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

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class MotiveDateTools {

	protected static $now = 0;
	/**
	 * Given optional start and end dates, return true if current date is between them. If any of
	 * the given dates are null, comprobation with this date will be true.
	 * @param int|null $start_date.
	 * @param int|null $end_date.
	 * @return bool true if today is between given dates.
	 */
	public static function is_today_between( $start_date, $end_date ) {
		if ( 0 === self::$now ) {
			self::$now = time();
		}
		$start_date = (int) $start_date;
		$end_date   = empty( $end_date ) ? PHP_INT_MAX : (int) $end_date;
		return self::$now > $start_date && self::$now < $end_date;
	}
}
