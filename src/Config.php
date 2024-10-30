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

namespace Motive\Woocommerce;

/**
 * Class Config
 * Enum and util class for all module configs
 * @package Motive\Prestashop
 * @method static set_token(string $randomToken)
 * @method static get_token()
 * @method static set_trigger_selector(string $selector)
 * @method static get_trigger_selector()
 * @method static set_engine_id(array $engineId)
 * @method static get_engine_id()
 * @method static set_time_limit(string $value)
 * @method static get_time_limit()
 * @method static set_image_size(string $value)
 * @method static get_image_size()
 * @method static set_features_limit(string $value)
 * @method static get_features_limit()
 * @method static set_activate_error_reporting(string $value)
 * @method static get_activate_error_reporting()
 * @method static set_layer_isolated(string $value)
 * @method static get_layer_isolated()
 * @method static set_shopper_prices(string $boolNum)
 * @method static get_shopper_prices()
 * @method static set_image_limit(string $value)
 * @method static get_image_limit()
 * @method static set_top_used_features(string $value)
 * @method static get_top_used_features()
 * @method static set_motive_x_url(string $value)
 * @method static get_motive_x_url()
 * @method static set_interoperability_url(string $value)
 * @method static get_interoperability_url()
 * @method static set_playboard_url(string $value)
 * @method static get_playboard_url()
 * @method static set_product_builder(string $value)
 * @method static get_product_builder()
 * @method static set_attribute_builder(string $value)
 * @method static get_attribute_builder()
 * @method static set_first_activation_date(string $value)
 * @method static get_first_activation_date()
 * @method static set_script_loader(string $value)
 * @method static get_script_loader()
 * @method static set_tagging_base_url(string $value)
 * @method static get_tagging_base_url()
 * @method static set_tagging_timeout(string $value)
 * @method static get_tagging_timeout()
 * @method static set_tagging_addtocart(string $value)
 * @method static get_tagging_addtocart()
 * @method static set_wp_rocket_bypass(string $value)
 * @method static get_wp_rocket_bypass()
 * @method static set_ff_unprefix_fields(string $value)
 * @method static get_ff_unprefix_fields()
 * @method static set_product_batch_size(string $value)
 * @method static get_product_batch_size()
 * @method static set_front_loader_url(string $url)
 * @method static get_front_loader_url()
 * @method static set_shopper_prices_endpoint_method(string $value)
 * @method static get_shopper_prices_endpoint_method()
 */
class Config {
	private static $query_params = array();

	const PREFIX     = 'MOTIVE_';
	const DEFINITION = array(
		'token'                          => array(
			'hidden'     => true,
			'persistent' => true,
		),
		'trigger_selector'               => array(),
		'engine_id'                      => array(
			'lang' => true,
		),
		'time_limit'                     => array(
			'default' => '30',
		),
		'image_size'                     => array(
			'default' => 'woocommerce_thumbnail',
		),
		'features_limit'                 => array(
			'default' => '100',
		),
		'activate_error_reporting'       => array(
			'default' => '0',
		),
		'layer_isolated'                 => array(
			'default' => '1',
		),
		'shopper_prices'                 => array(
			'default' => '1',
		),
		'last_sync_info'                 => array(
			'hidden' => true,
			'lang'   => true,
		),
		'image_limit'                    => array(
			'default' => '10',
		),
		'top_used_features'              => array(
			'hidden' => true,
			'lang'   => true,
		),
		'motive_x_url'                   => array(
			'default' => 'https://assets.motive.co/motive-x/v2/app.js',
		),
		'interoperability_url'           => array(
			'default' => 'https://assets.motive.co/motive-x/v2/interoperability.js',
		),
		'playboard_url'                  => array(
			'default' => 'https://playboard.motive.co/',
		),
		'product_builder'                => array(
			'default'        => 'Motive\\Woocommerce\\Builder\\Product\\MultipleQueryProductBuilder',
			'implementation' => true,
		),
		'attribute_builder'              => array(
			'default'        => 'Motive\\Woocommerce\\Builder\\Attribute\\PreciseAttributeBuilder',
			'implementation' => true,
		),
		'first_activation_date'          => array(
			'persistent' => true,
		),
		'script_loader'                  => array(
			'default'        => 'Motive\\Woocommerce\\ScriptLoaders\\ScriptFrontLoader',
			'implementation' => true,
		),
		'tagging_base_url'               => array(
			'default' => 'https://tagging-applications-0.api.motive.co/',
		),
		'tagging_timeout'                => array(
			'default' => '100',
		),
		'tagging_addtocart'              => array(
			'default' => 'QUERY_PARAM',
		),
		'wp_rocket_bypass'               => array(
			'default' => '0',
		),
		'ff_unprefix_fields'             => array(
			'default' => '1',
		),
		'product_batch_size'             => array(
			'default' => '1000',
		),
		'front_loader_url'               => array(
			'default' => 'https://assets.motive.co/front-loader/woocommerce/v1.js',
		),
		'shopper_prices_endpoint_method' => array(
			'default' => 'POST',
		),
	);

	// Configs Enum
	const TOKEN                          = 'MOTIVE_TOKEN';
	const TRIGGER_SELECTOR               = 'MOTIVE_TRIGGER_SELECTOR';
	const ENGINE_ID                      = 'MOTIVE_ENGINE_ID';
	const TIME_LIMIT                     = 'MOTIVE_TIME_LIMIT';
	const IMAGE_SIZE                     = 'MOTIVE_IMAGE_SIZE';
	const FEATURES_LIMIT                 = 'MOTIVE_FEATURES_LIMIT';
	const ACTIVATE_ERROR_REPORTING       = 'MOTIVE_ACTIVATE_ERROR_REPORTING';
	const LAYER_ISOLATED                 = 'MOTIVE_LAYER_ISOLATED';
	const SHOPPER_PRICES                 = 'MOTIVE_SHOPPER_PRICES';
	const LAST_SYNC_INFO                 = 'MOTIVE_LAST_SYNC_INFO';
	const IMAGE_LIMIT                    = 'MOTIVE_IMAGE_LIMIT';
	const TOP_USED_FEATURES              = 'MOTIVE_TOP_USED_FEATURES';
	const MOTIVE_X_URL                   = 'MOTIVE_MOTIVE_X_URL';
	const INTEROPERABILITY_URL           = 'MOTIVE_INTEROPERABILITY_URL';
	const PLAYBOARD_URL                  = 'MOTIVE_PLAYBOARD_URL';
	const PRODUCT_BUILDER                = 'MOTIVE_PRODUCT_BUILDER';
	const ATTRIBUTE_BUILDER              = 'MOTIVE_ATTRIBUTE_BUILDER';
	const FIRST_ACTIVATION_DATE          = 'MOTIVE_FIRST_ACTIVATION_DATE';
	const SCRIPT_LOADER                  = 'MOTIVE_SCRIPT_LOADER';
	const TAGGING_BASE_URL               = 'MOTIVE_TAGGING_BASE_URL';
	const TAGGING_TIMEOUT                = 'MOTIVE_TAGGING_TIMEOUT';
	const TAGGING_ADDTOCART              = 'MOTIVE_TAGGING_ADDTOCART';
	const WP_ROCKET_BYPASS               = 'MOTIVE_WP_ROCKET_BYPASS';
	const FF_UNPREFIX_FIELDS             = 'MOTIVE_FF_UNPREFIX_FIELDS';
	const PRODUCT_BATCH_SIZE             = 'MOTIVE_PRODUCT_BATCH_SIZE';
	const FRONT_LOADER_URL               = 'MOTIVE_FRONT_LOADER_URL';
	const SHOPPER_PRICES_ENDPOINT_METHOD = 'MOTIVE_SHOPPER_PRICES_ENDPOINT_METHOD';

	/**
	 * Uninstall defined configurations
	 */
	public static function uninstall() {
		$result = true;
		foreach ( array_keys( static::DEFINITION ) as $name ) {
			if ( ! static::is( $name, 'persistent' ) ) {
				$result = delete_option( static::name2key( $name ) ) && $result;
			}
		}
		return $result;
	}

	/**
	 * Magic method to handle get and set methods over configuration entries.
	 * @param $method
	 * @param $args
	 * @return mixed
	 * @throws Exception
	 */
	public static function __callStatic( $method, $args ) {
		$operation = substr( $method, 0, 3 );
		$name      = substr( $method, 4 );
		if ( array_key_exists( $name, static::DEFINITION ) ) {
			if ( static::is( $name, 'lang' ) ) {
				return static::call_static_lang( $operation, $name, $args );
			}
			if ( 'get' === $operation ) {
				return static::get( $name );
			} elseif ( 'set' === $operation ) {
				return update_option( static::name2key( $name ), $args[0] );
			}
		}

		return null;
	}

	/**
	 * Manages lang based configuration get/set.
	 * @param $operation
	 * @param $name
	 * @param $args
	 * @return mixed
	 * @throws Exception
	 */
	private static function call_static_lang( $operation, $name, $args = null ) {
		$results = array();
		foreach ( LanguageManager::get_instance()->langs as $lang ) {
			if ( 'get' === $operation ) {
				$results[ $lang ] = static::get( $name, $lang );
			} elseif ( 'set' === $operation && null !== $args && array_key_exists( $lang, $args[0] ) ) {
				return update_option( static::name2key( $name, $lang ), $args[0][ $lang ] );
			}
		}
		return $results;
	}

	/**
	 * Returns option value by name. If stored in db and not empty string, will return value from db.
	 * If not, if default value is set in configuration, default value will be returned. If not,
	 * an empty string will be returned.
	 * If config is a implementation, we will assure the class exist before returning.
	 * @param $name
	 * @param $lang
	 * @return mixed
	 */
	private static function get( $name, $lang = null ) {
		$query_param_key = "motive-$name" . ( null === $lang ? '' : "-$lang" );
		if ( isset( static::$query_params[ $query_param_key ] ) ) {
			return static::$query_params[ $query_param_key ];
		}
		$key           = static::name2key( $name, $lang );
		$default_value = array_key_exists( 'default', static::DEFINITION[ $name ] ) ? static::DEFINITION[ $name ]['default'] : '';
		$value         = get_option( $key, $default_value );
		$value         = '' === $value ? $default_value : $value;
		if ( static::is( $name, 'implementation' ) ) {
			if ( class_exists( $value ) ) {
				return $value;
			} else {
				return $default_value;
			}
		}
		return $value;
	}


	/**
	 * Update module configurations with the values in $config
	 * @param array $new_config
	 * @param boolean $only_if_unset
	 * @return array
	 */
	public static function import( array $new_config, $only_if_unset = false ) {
		$errors = array();
		foreach ( $new_config as $name => $value ) {
			try {
				static::import_one( $name, $value, $only_if_unset );
			} catch ( ConfigException $e ) {
				$errors[] = $e->getMessage();
			}
		}
		return $errors;
	}

	/**
	 * Import single configuration value
	 * @param string $name
	 * @param string|array $value
	 * @param boolean $only_if_unset
	 * @return string|null
	 * @throws ConfigException
	 */
	protected static function import_one( $name, $value, $only_if_unset ) {
		if ( ! array_key_exists( $name, static::DEFINITION ) ) {
			throw new ConfigException( esc_html( "Invalid configuration name '$name'" ) );
		}

		if ( is_array( $value ) ) {
			return static::import_one_lang( $name, $value, $only_if_unset );
		}

		$key = static::name2key( $name );

		if ( $only_if_unset && '' !== (string) get_option( $key ) ) {
			return;
		}

		update_option( $key, $value );
	}

	/**
	 * Import single configuration value for a catalog_code related configuration
	 * @param string $name
	 * @param array $value
	 * @param boolean $only_if_unset
	 * @return string|null
	 * @throws ConfigException
	 */
	protected static function import_one_lang( $name, $value, $only_if_unset ) {
		if ( is_array( $value ) !== static::is( $name, 'lang' ) ) {
			throw new ConfigException( esc_html( "Invalid configuration value for '$name'" ) );
		}
		$bad_catalog_codes = array();
		foreach ( $value as $catalog_code => $val ) {
			if ( ! in_array( $catalog_code, LanguageManager::get_instance()->langs, true ) ) {
				$bad_catalog_codes[] = $catalog_code;
			}
			$key = static::name2key( $name, $catalog_code );
			if ( $only_if_unset && '' !== (string) get_option( $key ) ) {
				continue;
			}
			update_option( $key, $value[ $catalog_code ] );
		}
		if ( ! empty( $bad_catalog_codes ) ) {
			$bad_catalog_codes = implode( ', ', $bad_catalog_codes );
			throw new ConfigException( esc_html( "Invalid code(s) '$bad_catalog_codes' for config '$name'" ) );
		}
		return null;
	}

	/**
	 * Export module configuration to array.
	 * @param bool $all include all configs, also hidden ones
	 * @param bool $raw return raw value from db instead of validated values
	 * @return array
	 */
	public static function export( $all = false, $raw = false ) {
		$result = array();

		foreach ( array_keys( static::DEFINITION ) as $name ) {
			if ( ! $all && static::is( $name, 'hidden' ) ) {
				continue;
			}

			if ( static::is( $name, 'lang' ) ) {
				$result[ $name ] = array();

				foreach ( LanguageManager::get_instance()->langs as $lang ) {
					$value = static::get_option_for_export( $name, $lang, $raw );
					if ( false !== $value ) {
						$result[ $name ][ $lang ] = $value;
					}
				}

				if ( 0 === count( $result[ $name ] ) ) {
					$result[ $name ] = '';
				}
			} else {
				$result[ $name ] = static::get_option_for_export( $name, null, $raw );
			}
		}
		return $result;
	}

	/**
	 * Wrapper for returning raw value from db or validated one for export.
	 * @param string $name option name
	 * @param string|null $lang lang if exists
	 * @param bool $raw return raw value from db instead of validated values
	 * @return string
	 */
	private static function get_option_for_export( $name, $lang = null, $raw = false ) {
		if ( $raw ) {
			return get_option( static::name2key( $name, $lang ) );
		}
		return static::get( $name, $lang );
	}

	/**
	 * Check if a property of config is true, because empty() does not work on class constants
	 * @param $name
	 * @param $property
	 * @return bool
	 */
	protected static function is( $name, $property ) {
		return array_key_exists( $name, static::DEFINITION )
			&& array_key_exists( $property, static::DEFINITION[ $name ] )
			&& true === static::DEFINITION[ $name ][ $property ];
	}

	/**
	 * Convert config name to Key format
	 * @param $name
	 * @param $lang
	 * @return string
	 */
	protected static function name2key( $name, $lang = null ) {
		return static::PREFIX . strtoupper( $name ) . ( null !== $lang ? '-' . strtoupper( $lang ) : '' );
	}

	/**
	 * Returns true if motive search layer is configured, which is checking non-emptyness engine id
	 * for current lang && trigger selector.
	 *
	 * @return boolean
	 */
	public static function is_enabled() {
		return ! empty( self::get_engine_id()[ LanguageManager::get_instance()->current_lang ] )
			&& ! empty( self::get_trigger_selector() );
	}

	/**
	 * Method to provide query params to config class from request.
	 */
	public static function set_query_params( $query_params ) {
		static::$query_params = $query_params;
	}
}
