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

namespace Motive\Woocommerce\Builder;

use Motive\Woocommerce\Model\Image;
use Motive\Woocommerce\Config;

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class ImageBuilder {

	protected $image_size;
	protected $image_limit;
	public function __construct() {
		$this->image_size  = Config::get_image_size();
		$this->image_limit = Config::get_image_limit();
		if ( false === $this->image_limit ) {
			$this->image_limit = 10;
		}
	}

	/**
	 * Returns images from its attachment ids
	 *
	 * @param Array $attachment_ids - List of attachment ids
	 *
	 * @return Image[]
	 */
	public function get_from_attachment_ids( $attachment_ids ) {
		$images = array();

		$results = array();
		if ( ! empty( $attachment_ids ) ) {
			$attachment_ids_imploded = implode( ',', $attachment_ids );
			global $wpdb;
			$results = $wpdb->get_results(
				"
                SELECT p.post_name as legend, p.id as attachment_id
                FROM {$wpdb->get_blog_prefix()}posts p
                WHERE (p.ID IN ($attachment_ids_imploded) AND p.post_type='attachment')
                ORDER BY FIELD(p.id, $attachment_ids_imploded)
                LIMIT $this->image_limit
          "
			);
		}

		foreach ( $results as $r ) {
			$url            = wp_get_attachment_image_src( $r->attachment_id, $this->image_size )[0];
			$images[ $url ] = Image::build( $url, $r->legend );
		}
		return array_values( $images );
	}
}
