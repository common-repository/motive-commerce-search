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

namespace Motive\Woocommerce\Model;

if ( ! defined( 'WC_VERSION' ) ) {
    exit;
}

/**
 * Class ProductLabel
 * @property string text - Optional text override.
 * @package Motive\Woocommerce\Model
 */
class ProductLabel {

    public $id;

    public static function build($id, $text = null)
    {
        $obj = new static();
        $obj->id = $id;
        if (!empty($text)) {
            $obj->text = $text;
        }
        return $obj;
    }
}
