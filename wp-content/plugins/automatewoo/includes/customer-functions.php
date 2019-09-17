<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * Simplified function for third-parties.
 *
 * @since 4.2
 *
 * @param string|int $email_or_user_id
 * @return bool
 */
function aw_is_customer_opted_in( $email_or_user_id ) {
	if ( is_numeric( $email_or_user_id ) ) {
		$customer = AutomateWoo\Customer_Factory::get_by_user_id( $email_or_user_id );
	}
	else {
		$customer = AutomateWoo\Customer_Factory::get_by_email( $email_or_user_id );
	}

	if ( ! $customer ) {
		return false;
	}

	return $customer->is_opted_in();
}


/**
 * @return int
 */
function aw_get_user_count() {

	if ( $cache = AutomateWoo\Cache::get_transient( 'user_count' ) )
		return $cache;

	global $wpdb;

	$count = absint( $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users" ) );

	AutomateWoo\Cache::set_transient( 'user_count', $count );

	return $count;
}


/**
 * Use if accuracy is not important, count is cached for a week
 * @return int
 */
function aw_get_user_count_rough() {

	if ( $cache = AutomateWoo\Cache::get_transient( 'user_count_rough' ) )
		return $cache;

	global $wpdb;

	$count = absint( $wpdb->get_var( "SELECT COUNT(ID) FROM $wpdb->users" ) );

	AutomateWoo\Cache::set_transient( 'user_count_rough', $count, 168 );

	return $count;
}


/**
 * @since 4.3
 *
 * @return AutomateWoo\Customer|bool
 */
function aw_get_logged_in_customer() {
	if ( ! is_user_logged_in() ) {
		return false;
	}
	return AutomateWoo\Customer_Factory::get_by_user_id( get_current_user_id() );
}

/**
 * Gets the user's first order.
 *
 * @param string|int   $email_or_user_id User email or id.
 * @param string|array $status           Order status we want to query.
 *                                       Defaults to paid statuses.
 *
 * @since 4.4
 *
 * @return bool|WC_Order
 */
function aw_get_customer_first_order( $email_or_user_id, $status = '' ) {
	$query_args = [
		'type'    => 'shop_order',
		'limit'   => 1,
		'orderby' => 'date',
		'order'   => 'ASC'
	];

	if ( empty( $status ) ) {
		$query_args['status'] = wc_get_is_paid_statuses();
	} else {
		$query_args['status'] = $status;
	}

	// Validate $email_or_user_id.
	if ( is_numeric( $email_or_user_id ) && $user_id = AutomateWoo\Clean::id( $email_or_user_id ) ) {
		$query_args['customer_id'] = $user_id;
	} elseif ( $email = AutomateWoo\Clean::email( $email_or_user_id ) ) {
		$query_args['customer'] = $email;
	} else {
		return false;
	}

	$orders = wc_get_orders( $query_args );

	if ( ! empty( $orders ) ) {
		return $orders[0];
	}

	return false;
}



/**
 * @deprecated
 *
 * @param  string $email
 * @return float
 */
function aw_get_total_spent_by_email( $email ) {
	wc_deprecated_function( __FUNCTION__, '4.6', 'AutomateWoo\Customer::get_total_spent()' );
	$customer = AutomateWoo\Customer_Factory::get_by_email( $email );
	return $customer->get_total_spent();
}

/**
 * @deprecated
 *
 * @since 2.7.1
 * @param int $user_id
 * @return int
 */
function aw_get_customer_order_count( $user_id ) {
	wc_deprecated_function( __FUNCTION__, '4.6', 'AutomateWoo\Customer::get_order_count()' );
	$customer = AutomateWoo\Customer_Factory::get_by_user_id( $user_id );
	return $customer->get_order_count();
}

/**
 * @deprecated
 *
 * @param string $email
 * @return int
 */
function aw_get_order_count_by_email( $email ) {
	wc_deprecated_function( __FUNCTION__, '4.6', 'AutomateWoo\Customer::get_order_count()' );
	$customer = AutomateWoo\Customer_Factory::get_by_email( $email );
	return $customer->get_order_count();
}

/**
 * @deprecated
 *
 * @since 3.9
 * @param int $user_id
 * @return array
 */
function aw_get_customer_order_ids( $user_id ) {
	wc_deprecated_function( __FUNCTION__, '4.6' );

	$ids = get_user_meta( $user_id, '_aw_order_ids', true );
	if ( '' === $ids ) {
		global $wpdb;

		$statuses = array_map( 'esc_sql', aw_get_counted_order_statuses( true ) );

		$ids = $wpdb->get_results( $wpdb->prepare( "SELECT post_id
			FROM $wpdb->posts as posts

			LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id

			WHERE   meta.meta_key       = '_customer_user'
			AND     posts.post_type     = 'shop_order'
			AND     posts.post_status   IN ('" . implode( "','", $statuses )  . "')
			AND     meta_value          = %s
		", $user_id ), OBJECT_K );

		$ids = AutomateWoo\Clean::ids( array_keys( $ids ) );

		update_user_meta( $user_id, '_aw_order_ids', $ids );
	}
	else {
		$ids = AutomateWoo\Clean::ids( $ids );
	}

	return $ids;
}

/**
 * @deprecated
 *
 * @param string $email
 * @return array
 */
function aw_get_customer_order_ids_by_email( $email ) {
	wc_deprecated_function( __FUNCTION__, '4.6' );

	if ( ! $email = AutomateWoo\Clean::email( $email ) ) {
		return 0;
	}

	global $wpdb;

	$statuses = array_map( 'esc_sql', aw_get_counted_order_statuses( true ) );

	$ids = $wpdb->get_results( $wpdb->prepare( "SELECT post_id
		FROM $wpdb->posts as posts

		LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id

		WHERE   meta.meta_key       = '_billing_email'
		AND     posts.post_type     = 'shop_order'
		AND     posts.post_status   IN ('" . implode( "','", $statuses )  . "')
		AND     meta.meta_value     = %s
	", $email ), OBJECT_K );

	return AutomateWoo\Clean::ids( array_keys( $ids ) );
}
