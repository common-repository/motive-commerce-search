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

/**
 * PluginUpdateManager will compare current version with stored one, in order to execute code needed
 * for plugin updates. After this execution, will update stored version to current one.
 * Introduced in v1.19.0 along with scripts loader.
 */
class PluginUpdateManager {
	const STORED_VERSION = 'MOTIVE_STORED_VERSION';
	public static function init() {
		$stored_version = get_option( self::STORED_VERSION );
		if ( ! $stored_version ) {
			// New install or too old client.
			update_option( self::STORED_VERSION, MOTIVE_VERSION );
			return;
		}
		if ( version_compare( $stored_version, '1.25.0', '<' ) ) {
			// v1.25.0, shopper prices not using intermediate post anymore.
			self::shopper_prices_not_using_intermediate_post();
			// v1.25.0, prefix of attributes & features should be maintained for previous installations.
			Config::set_ff_unprefix_fields( '0' );
		}
		if ( MOTIVE_VERSION !== $stored_version ) {
			// And at the end, if stored version was not equal to current, we will update stored version
			// to current one.
			update_option( self::STORED_VERSION, MOTIVE_VERSION );
		}
	}

	/**
	 * After 1.24.0 shopper prices intermediate post is not anymore required, so we will delete
	 * the created posts.
	 */
	private static function shopper_prices_not_using_intermediate_post() {
		global $wpdb;
		$sql = "DELETE FROM {$wpdb->get_blog_prefix()}_post WHERE post_type='motive_data_post'";
		$wpdb->get_results( $sql );
	}
}
