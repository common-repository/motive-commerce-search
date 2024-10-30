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


class Token {
	/**
	 * Create token if not exist inside options
	 */
	public static function install() {
		if ( empty( get_option( Config::TOKEN ) ) ) {
			self::create_token();
		}
	}

	/**
	 * Compares provided token with stored one to allow access.
	 * If no stored token, or is empty, will always return false.
	 * @param $token_to_check - Token to compare against the stored one.
	 *
	 * @return bool - True if same token, False otherwise
	 */
	public static function check( $token_to_check ) {
		$stored_token = get_option( Config::TOKEN, '' );
		return ! empty( $stored_token ) && $stored_token === $token_to_check;
	}

	/**
	 * Regenerate token
	 */
	public static function regenerate() {
		return self::create_token();
	}

	/**
	 * Creates token, saves it, and returns its value
	 */
	private static function create_token() {
		$token = 'wc-' . wp_generate_password( 32, false, false );
		update_option( Config::TOKEN, $token );
		return $token;
	}
}
