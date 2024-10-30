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
 * ScriptInlineEnqueuer will try to insert layer initialization js as a inline script.
 * In order to be able to do that, a build step was added to js, which wraps the js in a php file,
 * as wp lint rules doesn't like the use of `file_get_contents` (and there are many wp with file
 * system permission issues).
 *
 * We also register & enqueue a empty src script, to be able to register inline javascript with
 * `wp_add_inline_script` function.
 */
class ScriptInlineEnqueuer extends AbstractScriptEnqueuer {
	public function enqueue_scripts_layer() {
		$config = static::get_front_config();
		// In order to write js initialization as a script inline, we need to enqueue a empty script.
		wp_enqueue_script( $this->plugin_name, $this->fake_script_src, array( 'jquery' ), $this->version, false );
		// Having the script registered, we can write before it the init vars.
		wp_add_inline_script( $this->plugin_name, 'const motive = ' . wp_json_encode( $config ) . ';', 'before' );
		wp_add_inline_script( $this->plugin_name, 'const motiveShopperPricesNonce = ' . wp_json_encode( $config['nonce'] ) . ';', 'before' );
		// And after it, we can write after it our js.
		wp_add_inline_script( $this->plugin_name, $this->get_js_as_string() . ';', 'after' );
	}
	public function script_tag_interceptor( $tag, $handle, $source ) {
		$tag = parent::script_tag_interceptor( $tag, $handle, $source );
		if ( $handle !== $this->plugin_name ) {
			return $tag;
		}
		// phpcs:disable
		$tag = preg_replace( "/<script.*src=[\"'].*$this->fake_script_src.*<\/script>/", '', $tag );
		// phpcs:enable
		return $tag;
	}
	/**
	 * Returns js content as string. Required build step
	 */
	private function get_js_as_string() {
		// file_get_contents is forbidden so the js file is wrapped in a PHP file with the content
		return require __DIR__ . '/../../public/js/motive-public.js.php';
	}
}
