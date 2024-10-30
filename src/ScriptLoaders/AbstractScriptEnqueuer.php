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
 * This abstract class have common functionality for script loader which use `wp_enqueue_script`
 * native function. Both of them share how interoperability is added, but differs on the layer
 * initialization scripts strategy.
 */
abstract class AbstractScriptEnqueuer extends ScriptLoader {
	protected $fake_script_src = 'motive-fake-script-src';
	protected function __construct( $loader, $plugin_name, $version ) {
		parent::__construct( $loader, $plugin_name, $version );
		$loader->add_filter( 'script_loader_tag', $this, 'script_tag_interceptor', 10, 3 );
	}

	public function script_tag_interceptor( $tag, $handle, $source ) {
		if ( empty( $this->extra_script_attr ) || 0 !== strpos( $this->plugin_name, $handle ) ) {
			return $tag;
		}
		$tag = str_replace( '<script ', "<script {$this->extra_script_attr}", $tag );
		return $tag;
	}

	public function add_layer_js() {
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts_layer' );
	}

	abstract public function enqueue_scripts_layer();

	public function add_interoperability_js() {
		$this->loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_scripts_interoperability' );
	}

	public function enqueue_scripts_interoperability() {
		wp_enqueue_script( $this->plugin_name . '-interoperability', Config::get_interoperability_url(), array(), $this->version, false );
	}
}
