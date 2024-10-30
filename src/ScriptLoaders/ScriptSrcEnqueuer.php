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

/**
 * ScriptSrcEnqueuer will try to insert layer initialization js as a script with src pointing to
 * motive-public.js file.
 * Using `wp_add_inline_script` we add init params & nonce before the file, in order to be ready to
 * the script load.
 */
class ScriptSrcEnqueuer extends AbstractScriptEnqueuer {

	public function enqueue_scripts_layer() {
		$config = static::get_front_config();
		wp_enqueue_script( $this->plugin_name, $this->script_url, array( 'jquery' ), $this->version, false );
		wp_add_inline_script( $this->plugin_name, 'const motive = ' . wp_json_encode( $config ) . ';', 'before' );
		wp_add_inline_script( $this->plugin_name, 'const motiveShopperPricesNonce = ' . wp_json_encode( $config['nonce'] ) . ';', 'before' );
	}
}
