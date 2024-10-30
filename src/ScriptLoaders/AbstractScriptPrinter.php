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


namespace Motive\Woocommerce\ScriptLoaders;

use Motive\Woocommerce\ScriptLoader;
use Motive\Woocommerce\Config;

/**
 * AbstractScriptPrinter loader have common functionality for script loaders bypassing WP official
 * js loaders.
 */
abstract class AbstractScriptPrinter extends ScriptLoader {
	abstract public function add_layer_js();

	public function print_scripts_layer() {
		$config = static::get_front_config();
		// phpcs:disable
		printf( "<script %s id='%s-before-motive'>const motive = %s;</script>", esc_attr( $this->extra_script_attr ), esc_attr( $this->plugin_name ), wp_json_encode( $config ) );
		printf( "<script %s id='%s-before-motive-nonce'>const motiveShopperPricesNonce = %s;</script>", esc_attr( $this->extra_script_attr ), esc_attr( $this->plugin_name ), wp_json_encode( $config['nonce'] ) );
		printf( "<script %s id='%s' src='%s?ver=%s'></script>", esc_attr( $this->extra_script_attr ), esc_attr( $this->plugin_name ), esc_url( $this->script_url ), esc_attr( $this->version ) );
		// phpcs:enable
	}

	abstract public function add_interoperability_js();

	public function print_scripts_interoperability() {
		// phpcs:disable
		printf( "<script %s id='%s-interoperability' src='%s?ver=%s'></script>", esc_attr( $this->extra_script_attr ), esc_attr( $this->plugin_name ), esc_url( Config::get_interoperability_url() ), esc_attr( $this->version ) );
		// phpcs:enable
	}
}
