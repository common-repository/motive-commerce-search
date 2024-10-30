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
 * ScriptHeadPrinter loader will try to append scripts for layer using `wp_head` action, with a
 * priority of 2 (should be less than the one used for preload links). This action is provided by
 * WordPress and the active theme is the one responsible on writing what `wp_head` says.
 */
class ScriptHeadPrinter extends AbstractScriptPrinter {

	public function add_layer_js() {
		$this->loader->add_action( 'wp_head', $this, 'print_scripts_layer', 2 );
	}

	public function add_interoperability_js() {
		$this->loader->add_action( 'wp_head', $this, 'print_scripts_interoperability', 2 );
	}
}
