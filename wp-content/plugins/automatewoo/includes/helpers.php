<?php
// phpcs:ignoreFile

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Gets a variable from the $_GET array but checks if it's set first.
 *
 * @since 4.4.0
 *
 * @param string $param
 *
 * @return mixed
 */
function aw_get_url_var( $param ) {
	if ( isset( $_GET[ $param ] ) ) {
		return $_GET[ $param ];
	}
	return false;
}


/**
 * Gets a variable from the $_POST array but checks if it's set first.
 *
 * @since 4.4.0
 *
 * @param string $param
 *
 * @return mixed
 */
function aw_get_post_var( $param ) {
	if ( isset( $_POST[ $param ] ) ) {
		return $_POST[ $param ];
	}
	return false;
}


/**
 * Gets a variable from the $_REQUEST array but checks if it's set first.
 *
 * @param $param
 * @return mixed
 */
function aw_request( $param ) {
	if ( isset( $_REQUEST[ $param ] ) ) {
		return $_REQUEST[ $param ];
	}
	return false;
}



/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * @deprecated
 * @param string|array $var
 * @return string|array
 */
function aw_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'aw_clean', $var );
	}
	else {
		return is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
	}
}


/**
 * @deprecated
 * @param $email
 * @return string
 */
function aw_clean_email( $email ) {
	return strtolower( sanitize_email( $email ) );
}



/**
 * @param $type string
 * @param $item
 *
 * @return mixed item of false
 */
function aw_validate_data_item( $type, $item ) {

	if ( ! $type || ! $item )
		return false;

	$valid = false;

	// Validate with the data type classes
	if ( $data_type = AutomateWoo\Data_Types::get( $type ) ) {
		$valid = $data_type->validate( $item );
	}

	/**
	 * @since 2.1
	 */
	$valid = apply_filters( 'automatewoo_validate_data_item', $valid, $type, $item );

	if ( $valid ) return $item;

	return false;
}



/**
 * This is much like wc_get_template() but won't fail if the default template file is missing
 *
 * @param string $template_name
 * @param array $imported_variables (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function aw_get_template( $template_name, $imported_variables = [], $template_path = '', $default_path = '' ) {

	if ( ! $template_path ) $template_path = 'automatewoo/';
	if ( ! $default_path ) $default_path = AW()->path( '/templates/' );

	if ( $imported_variables && is_array( $imported_variables ) ) {
		extract( $imported_variables );
	}

	$located = wc_locate_template( $template_name, $template_path, $default_path );

	if ( file_exists( $located ) ) {
		include $located;
	}

}


/**
 * @deprecated
 * @param int $timestamp
 * @param bool|int $max_diff
 * @param bool $convert_from_gmt
 * @return string
 */
function aw_display_date( $timestamp, $max_diff = false, $convert_from_gmt = true ) {
	return AutomateWoo\Format::date( $timestamp, $max_diff, $convert_from_gmt );
}


/**
 * @deprecated
 * @param int $timestamp
 * @param bool|int $max_diff
 * @param bool $convert_from_gmt If its gmt convert it to site time
 * @return string|false
 */
function aw_display_time( $timestamp, $max_diff = false, $convert_from_gmt = true ) {
	return AutomateWoo\Format::datetime( $timestamp, $max_diff, $convert_from_gmt );
}


/**
 * @param $length int
 * @param bool $case_sensitive When false only lowercase letters will be included
 * @param bool $more_numbers
 * @return string
 */
function aw_generate_key( $length = 25, $case_sensitive = true, $more_numbers = false ) {

	$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';

	if ( $case_sensitive ) {
		$chars .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	}

	if ( $more_numbers ) {
		$chars .= '01234567890123456789';
	}

	$password = '';
	$chars_length = strlen( $chars );

	for ( $i = 0; $i < $length; $i++ ) {
		$password .= substr($chars, wp_rand( 0, $chars_length - 1), 1);
	}

	return $password;
}


/**
 * Generates a random key string for unique coupons.
 *
 * Doesn't use ambiguous characters like: 0 o i l 1.
 * Doesn't run any queries to check if the coupon is actually unique.
 *
 * @since 4.3.0
 *
 * @param int $length
 * @return string
 */
function aw_generate_coupon_key( $length = 10 ) {
	$chars = 'abcdefghjkmnpqrstuvwxyz23456789';
	$coupon_key = '';
	$chars_length = strlen( $chars );

	for ( $i = 0; $i < $length; $i++ ) {
		$coupon_key .= substr($chars, wp_rand( 0, $chars_length - 1), 1);
	}

	return $coupon_key;
}


/**
 * @param $price
 * @return float
 */
function aw_price_to_float( $price ) {

	$price = html_entity_decode( str_replace(',', '.', $price ) );

	$price = preg_replace( "/[^0-9\.]/", "", $price );

	return (float) $price;
}

/**
 * Get status to use when counting customer orders.
 *
 * This function will never return an empty array.
 *
 * @param bool $include_prefix
 *
 * @return array
 *
 * @since 2.7.1
 */
function aw_get_counted_order_statuses( $include_prefix = true ) {
	$default_statuses = array_merge( wc_get_is_paid_statuses(), [ 'on-hold' ] );
	$statuses         = array_filter( apply_filters( 'automatewoo/counted_order_statuses', $default_statuses ) );

	if ( ! $statuses ) {
		$statuses = $default_statuses;
	}

	if ( $include_prefix ) {
		$statuses = array_map( 'aw_add_order_status_prefix', $statuses );
	}

	return $statuses;
}


/**
 * @since 3.5.1
 * @param string $status
 * @return string
 */
function aw_add_order_status_prefix( $status ) {
	return 'wc-' . $status;
}


/**
 * @param $order WC_Order
 * @return array
 */
function aw_get_order_cross_sells( $order ) {

	$cross_sells = [];
	$in_order = [];

	$items = $order->get_items();

	foreach ( $items as $item ) {
		$product = AutomateWoo\Compat\Order::get_product_from_item( $order, $item );
		$in_order[] = AutomateWoo\Compat\Product::is_variation( $product ) ? AutomateWoo\Compat\Product::get_parent_id( $product ) : AutomateWoo\Compat\Product::get_id( $product );
		$cross_sells = array_merge( AutomateWoo\Compat\Product::get_cross_sell_ids( $product ), $cross_sells );
	}

	return array_diff( $cross_sells, $in_order );
}


/**
 * @param $array
 * @param $value
 * @return void
 */
function aw_array_remove_value( &$array, $value ) {
	if ( ( $key = array_search( $value, $array ) ) !== false ) {
		unset( $array[$key] );
	}
}


/**
 * Removes an item by key from array and returns its value.
 *
 * @param $array
 * @param $key
 * @return mixed
 */
function aw_array_extract( &$array, $key ) {
	if ( ! is_array( $array ) || ! isset( $array[ $key ] ) ) {
		return false;
	}

	$var = $array[ $key ];
	unset( $array[ $key ] );

	return $var;
}


/**
 * @param $array
 * @param $key
 * @return array
 */
function aw_array_move_to_end( $array, $key ) {
	$val = aw_array_extract( $array, $key );
	$array[$key] = $val;
	return $array;
}


/**
 * str_replace but limited to one replacement
 * @param string$subject
 * @param string$find
 * @param string $replace
 * @return string
 */
function aw_str_replace_first_match( $subject, $find, $replace = '' ) {
	$pos = strpos($subject, $find);
	if ($pos !== false) {
		return substr_replace($subject, $replace, $pos, strlen($find));
	}
	return $subject;
}


/**
 * @deprecated
 * @param string $subject
 * @param string $find
 * @param string $replace
 * @return string
 */
function aw_str_replace_start( $subject, $find, $replace = '' ) {
	return aw_str_replace_first_match( $subject, $find, $replace = '' );
}

/**
 * Determine if a string starts with another string.
 *
 * @since 4.6.0
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
function aw_str_starts_with( $haystack, $needle ) {
	return substr( $haystack, 0, strlen( $needle ) ) === $needle;
}

/**
 * Determine if a string ends with another string.
 *
 * @since 4.6.0
 *
 * @param string $haystack
 * @param string $needle
 *
 * @return bool
 */
function aw_str_ends_with( $haystack, $needle ) {
	$length = strlen( $needle );

	if ( $length == 0 ) {
		return true;
	}

	return substr( $haystack, -$length ) === $needle;
}

/**
 * Get user agent string.
 * This function can be removed when WC 3.0.0 or greater is required.
 * @since  3.3.1
 * @return string
 */
function aw_get_user_agent() {
	return isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( $_SERVER['HTTP_USER_AGENT'] ) : '';
}


/**
 * Define cache blocking constants if not already defined
 * @since 3.6.0
 */
function aw_set_nocache_constants() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( "DONOTCACHEPAGE", true );
	}
	if ( ! defined( 'DONOTCACHEOBJECT' ) ) {
		define( "DONOTCACHEOBJECT", true );
	}
	if ( ! defined( 'DONOTCACHEDB' ) ) {
		define( "DONOTCACHEDB", true );
	}
}


/**
 * Wrapper for nocache_headers which also disables page caching but allows object caching.
 *
 * @since 4.4.0
 */
function aw_no_page_cache() {
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( "DONOTCACHEPAGE", true );
	}
	nocache_headers();
}


/**
 * Get sanitized URL query args.
 *
 * @since 3.6.0
 * @param array $excluded Option to exclude some params
 * @return array
 */
function aw_get_query_args( $excluded = [] ) {
	$params = AutomateWoo\Clean::recursive( $_GET );

	foreach( $excluded as $key ) {
		unset( $params[ $key ] );
	}

	return $params;
}


/**
 * @since 3.6.1
 * @param string $country_code
 * @return string|bool
 */
function aw_get_country_name( $country_code ) {
	$countries = WC()->countries->get_countries();
	return isset( $countries[ $country_code ] ) ? $countries[ $country_code ] : false;
}


/**
 * @since 3.6.1
 * @param string $country_code
 * @param string $state_code
 * @return string|bool
 */
function aw_get_state_name( $country_code, $state_code ) {
	$states = WC()->countries->get_states( $country_code );
	return isset( $states[ $state_code ] ) ? $states[ $state_code ] : false;
}


/**
 * @since 3.8
 * @param mixed $val
 * @return int
 */
function aw_bool_int( $val ) {
	return intval( (bool) $val );
}


/**
 * @since 4.0
 * @param string $email
 * @return string
 */
function aw_anonymize_email( $email ) {
	if ( ! is_email( $email ) ) {
		return '';
	}
	$s1 = explode( '@', $email );
	$s2 = explode( '.', $s1[1], 2 );

	$anonymized = _aw_anonymize_email_part( $s1[0] ) . '@' . _aw_anonymize_email_part( $s2[0] ) . '.' . $s2[1];

	return apply_filters( 'automatewoo/anonymize_email', $anonymized, $email );
}


/**
 * @since 4.0
 * @param string $part
 * @return string
 */
function _aw_anonymize_email_part( $part ) {
	$to_keep = 2;
	$star_length = max( strlen( $part ) - $to_keep, 3 ); // min length of 3 stars
	return substr( $part, 0, $to_keep ) . str_repeat( '*', $star_length );
}


/**
 * @since 4.0
 * @param $email
 * @return bool
 */
function aw_is_email_anonymized( $email ) {
	if ( $email == 'deleted@site.invalid' ) {
		return true;
	}

	if ( strstr( $email, '***' ) !== false ) {
		return true;
	}

	return false;
}


/**
 * @since 4.1
 * @param $thing
 * @return bool
 */
function aw_is_error( $thing ) {
	return ( $thing instanceof AutomateWoo\Error || $thing instanceof WP_Error );
}


/**
 * Version can have a max of 3 parts, e.g. 4.1.0.1, isn't supported.
 * Max value of a single part is 999.
 *
 * @since 4.2
 * @param string $version
 * @return int
 */
function aw_version_str_to_int( $version ) {
	$parts = array_map( 'absint', explode( '.', (string) $version ) ); // convert to int here to remove any extra version info
	$padded = $parts[0]
		. str_pad( isset( $parts[1] ) ? $parts[1] : 0, 3, '0', STR_PAD_LEFT )
		. str_pad( isset( $parts[2] ) ? $parts[2] : 0, 3, '0', STR_PAD_LEFT );
	return (int) $padded;
}


/**
 * @since 4.2
 * @param int $version
 * @return string
 */
function aw_version_int_to_str( $version ) {
	$version = (string) (int) $version; // parse as int before convert to string
	$length = strlen( $version );

	if ( $length < 7 ) {
		return '0.0.0'; // incorrect format
	}

	$part3 = (int) substr( $version, -3, 3 );
	$part2 = (int) substr( $version, -6, 3 );
	$part1 = (int) substr( $version, 0, 3 - ( 9 - $length ) );
	return "$part1.$part2.$part3";
}


/**
 * Converts a date object to a mysql formatted string.
 *
 * WC_Datetime objects are converted to UTC timezone.
 *
 * @since 4.4.0
 *
 * @param WC_DateTime|DateTime|AutomateWoo\DateTime $date
 *
 * @return string|false
 */
function aw_date_to_mysql_string( $date ) {
	if ( $date = aw_normalize_date( $date ) ) {
		return $date->to_mysql_string();
	}

	return false;
}


/**
 * Convert a date object to an instance of AutomateWoo\DateTime.
 *
 * WC_Datetime objects are converted to UTC timezone.
 *
 * @since 4.4.0
 *
 * @param WC_DateTime|DateTime|AutomateWoo\DateTime|string $input
 *
 * @return AutomateWoo\DateTime|false
 */
function aw_normalize_date( $input ) {
	if ( ! $input ) {
		return false;
	}

	try {
		if ( is_numeric( $input ) ) {
			$new = new AutomateWoo\DateTime();
			$new->setTimestamp( $input );
			return $new;
		}

		if ( is_string( $input ) ) {
			$new = new AutomateWoo\DateTime( $input );
			return $new;
		}

		if ( is_a( $input, 'AutomateWoo\DateTime' ) ) {
			return $input;
		}

		if ( is_a( $input, 'WC_DateTime' ) || is_a( $input, 'DateTime' ) ) {
			$new = new AutomateWoo\DateTime();
			$new->setTimestamp( $input->getTimestamp() );
			return $new;
		}
	} catch( \Exception $e ) {
		return false;
	}

	return false;
}


/**
 * Convert a date string to a WC_DateTime.
 *
 * Based on wc_string_to_datetime(), introduced in WooCommerce 3.1.0.
 *
 * @since  4.4.0
 * @param  string $time_string Time string.
 * @return WC_DateTime
 */
function aw_string_to_wc_datetime( $time_string ) {
	if ( function_exists( 'wc_string_to_datetime' ) ) {
		return wc_string_to_datetime( $time_string );
	} else {
		// Strings are defined in local WP timezone. Convert to UTC.
		if ( 1 === preg_match( '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(Z|((-|\+)\d{2}:\d{2}))$/', $time_string, $date_bits ) ) {
			$offset    = ! empty( $date_bits[7] ) ? iso8601_timezone_to_offset( $date_bits[7] ) : wc_timezone_offset();
			$timestamp = gmmktime( $date_bits[4], $date_bits[5], $date_bits[6], $date_bits[2], $date_bits[3], $date_bits[1] ) - $offset;
		} else {
			$timestamp = wc_string_to_timestamp( get_gmt_from_date( gmdate( 'Y-m-d H:i:s', wc_string_to_timestamp( $time_string ) ) ) );
		}
		$datetime = new WC_DateTime( "@{$timestamp}", new DateTimeZone( 'UTC' ) );

		// Set local timezone or offset.
		if ( get_option( 'timezone_string' ) ) {
			$datetime->setTimezone( new DateTimeZone( wc_timezone_string() ) );
		} else {
			$datetime->set_utc_offset( wc_timezone_offset() );
		}

		return $datetime;
	}
}

/**
 * Get an array of post statuses that a post can have while being a draft.
 *
 * Note that 'draft' is deliberately not included based on how WC uses this status.
 *
 * @since 4.4.0
 *
 * @return array
 */
function aw_get_draft_post_statuses() {
	return [ 'auto-draft', 'new', 'wc-auto-draft' ];
}
