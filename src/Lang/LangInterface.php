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

namespace Motive\Woocommerce\Lang;

interface LangInterface {

	public function get_default_language();
	public function get_current_language();
	public function get_language_url( $lang, $name );
	public function get_language_list();
	public function get_language_list_with_locale();
	public function get_inner_join_products_lang( $lang );
	public function get_label( $id, $product_attribute, $lang );
	public function get_thumbnail_id( $object_id );
	public function get_product_image_gallery( $object_id );
}
