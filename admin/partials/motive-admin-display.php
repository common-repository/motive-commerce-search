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

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.motive.co
 * @since      1.0.0
 *
 * @package    Motive
 * @subpackage Motive/admin/partials
 */
use Motive\Woocommerce\LanguageManager;
use Motive\Woocommerce\Config;
$lang_manager = LanguageManager::get_instance();

?>

<mot-configuration-page id="mot-configuration-page"
	locale="<?php echo esc_attr( $lang_manager->current_lang ); ?>"
	token="<?php echo esc_attr( get_option( Motive\Woocommerce\Config::TOKEN ) ); ?>"
	platform="WooCommerce"
	version="<?php echo esc_attr( $this->version ); ?>"
	is-configured="<?php echo esc_attr( ! empty( Config::get_trigger_selector() ) ); ?>"
	is-enabled="<?php echo esc_attr( Config::is_enabled() ); ?>"
>
</mot-configuration-page>
<script type="text/javascript">
	var nonce = "<?php echo esc_attr( wp_create_nonce( 'wp_rest' ) ); ?>";
	var baseUrl = "<?php echo esc_attr( get_site_url() ); ?>";
	document.getElementById('mot-configuration-page').onRegenerateToken = function () {
		return fetch(baseUrl + '?rest_route=/motive/admin/regenerate-token', {
			method: 'GET',
			headers: { 'X-WP-Nonce': nonce }
		}).then(res => res.json());
	}
</script>
