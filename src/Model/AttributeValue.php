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

if (!defined('WC_VERSION')) {
    exit;
}

class AttributeValue
{
    /** @var string Attribute's normalized field name */
    public $key;

    /** @var string Attribute's value */
    public $value;

    /** @var string Attribute's optional color or texture url */
    public $decoration;

    /**
     * MotiveAttribute constructor.
     * @param string $key
     * @param string $value
     * @param string|null $decoration
     */
    public function __construct($key, $value, $decoration = null)
    {
        $this->key = $key;
        $this->value = $value;
        $this->decoration = $decoration;
    }
}
