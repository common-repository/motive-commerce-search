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

use Exception;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

/**
 * Class TimeLimit
 * Helps to track script duration to avoid timeouts and max execution time limits
 * @package Motive\Prestashop
 */
class TimeLimit {

	protected $cpu_time_limit;
	protected $real_time_limit;
	protected $cpu_ignored_time = 0;
	protected $use_real_time    = false;

	/**
	 * TimeLimit constructor.
	 * @param int|null $cpu_time_limit in seconds
	 * @param int $realTimeLimit in seconds
	 */
	public function __construct( $cpu_time_limit = null, $real_time_limit = 300 ) {
		$this->cpu_time_limit  = (int) ini_get( 'max_execution_time' );
		$this->real_time_limit = $real_time_limit;

		$php_os           = PHP_OS;
		$is_windows       = 'W' === $php_os[0];
		$rusage_available = function_exists( 'getrusage' );

		// If getrusage is not available, or on Windows before PHP 7.0: The real time is measured.
		$this->use_real_time = ! $rusage_available || ( $is_windows && PHP_MAJOR_VERSION < 7 );

		if ( null !== $cpu_time_limit ) {
			$this->set_cpu_time_limit( $cpu_time_limit );
		}
	}

	/**
	 * @return int
	 */
	public function get_real_time_limit() {
		return $this->real_time_limit;
	}

	/**
	 * @param int $real_time_limit
	 */
	public function set_real_time_limit( $real_time_limit ) {
		$this->real_time_limit = $real_time_limit;
	}

	/**
	 * @return int
	 */
	public function get_cpu_time_limit() {
		return $this->cpu_time_limit;
	}

	/**
	 * @param int $seconds
	 */
	public function set_cpu_time_limit( $seconds ) {
		if ( $seconds < 0 ) {
			$seconds = 0;
		}

		$this->cpu_ignored_time += $this->elapsed_cpu_time();

		set_time_limit( $seconds );
		$this->cpu_time_limit = (int) ini_get( 'max_execution_time' );
	}

	/**
	 * @return int remaining CPU time in seconds
	 */
	public function remaining_time() {
		try {
			return min( $this->remaining_cpu_time(), $this->remaining_real_time() );
		} catch ( Exception $e ) {
			return 0;
		}
	}

	/**
	 * @return int remaining CPU time in seconds
	 * @throws TimeLimitChangedException
	 */
	public function remaining_cpu_time() {
		if ( (int) ini_get( 'max_execution_time' ) !== $this->cpu_time_limit ) {
			throw new TimeLimitChangedException( 'CPU time limit changed without using this class.', 1 );
		}

		// No CPU time limitation
		if ( 0 === $this->cpu_time_limit ) {
			return (float) PHP_INT_MAX;
		}

		return $this->cpu_time_limit - $this->elapsed_cpu_time();
	}

	/**
	 * @return float elapsed CPU time in seconds
	 */
	public function elapsed_cpu_time() {
		if ( $this->use_real_time ) {
			return ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ) - $this->cpu_ignored_time;
		}

		// Any time spent on activity that happens outside the execution of the script is not included.
		// @see http://php.net/manual/en/function.set-time-limit.php
		$usages = getrusage();
		return $usages['ru_stime.tv_sec'] + $usages['ru_utime.tv_usec'] / 1000000 - $this->cpu_ignored_time;
	}

	/**
	 * @return float remaining real time in seconds
	 */
	public function remaining_real_time() {
		return $this->real_time_limit - $this->elapsed_real_time();
	}

	/**
	 * @return float elapsed Real time in seconds
	 */
	public function elapsed_real_time() {
		return microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'];
	}
}
