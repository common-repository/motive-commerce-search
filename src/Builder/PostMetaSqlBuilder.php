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

if ( ! defined( 'WC_VERSION' ) ) {
	exit;
}

class PostMetaSqlBuilder {
	private static function get_single_post_meta_sql( $meta_name, $db_prefix, $join = 'LEFT', $alias_prefix = 'p' ) {
		$select = "pm$meta_name.meta_value AS $alias_prefix$meta_name";
		$join   = "$join JOIN {$db_prefix}postmeta AS pm$meta_name ON pm$meta_name.meta_id = (SELECT MAX(meta_id) FROM {$db_prefix}postmeta WHERE meta_key = '$meta_name' AND post_id = p.ID)";

		return (object) array(
			'select' => $select,
			'join'   => $join,
		);
	}

	public static function get_post_meta_sql( $metas, $prefix, $join = 'LEFT', $alias_prefix = 'p' ) {
		$select_statements = array();
		$joins_statements  = array();
		foreach ( $metas as $meta ) {
			$meta_sql            = self::get_single_post_meta_sql( $meta, $prefix, $join, $alias_prefix );
			$select_statements[] = $meta_sql->select;
			$joins_statements[]  = $meta_sql->join;
		}

		return (object) array(
			'select_statement' => join( ',', $select_statements ),
			'join_statement'   => join( "\n", $joins_statements ),
		);
	}
}
