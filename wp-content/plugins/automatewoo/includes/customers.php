<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Customer (including guests) management class
 *
 * @class Customers
 * @since 3.0.0
 */
class Customers {


	static function init() {
		$self = 'AutomateWoo\Customers'; /** @var $self Customers */

		add_action( 'automatewoo/object/delete', [ $self, 'delete_customer_on_guest_delete' ] );
		add_action( 'delete_user', [ $self, 'delete_customer_on_user_delete' ] );
		add_action( 'user_register', [ $self, 'maybe_update_guest_customer_when_user_registers' ], 5 );
		add_action( 'automatewoo_updated_async', [ $self, 'setup_registered_customers' ] );
		add_action( 'automatewoo_setup_registered_customers', [ $self, 'setup_registered_customers' ] );
		add_action( 'automatewoo_setup_guest_customers', [ $self, 'maybe_setup_guest_customers' ] );
		add_action( 'automatewoo_four_hourly_worker', [ $self, 'maybe_setup_guest_customers' ] ); // fallback

		add_action( 'clean_comment_cache', [ $self, 'clean_review_count_cache_on_clean_comment_cache' ] );

		add_action( 'woocommerce_order_status_changed', [ $self, 'maybe_update_customer_last_order_date' ], 20, 3 );
		add_action( 'woocommerce_order_status_changed', [ $self, 'maybe_update_guest_most_recent_order' ], 20, 3 ); // don't use async
	}


	/**
	 * @param Model|Guest $object
	 */
	static function delete_customer_on_guest_delete( $object ) {
		if ( $object->object_type !== 'guest' ) {
			return;
		}

		if ( $customer = Customer_Factory::get_by_guest_id( $object->get_id(), false ) ) {
			$customer->delete();
		}
	}


	/**
	 * @param int $user_id
	 */
	static function delete_customer_on_user_delete( $user_id ) {
		if ( ! $user_id ) {
			return;
		}

		if ( $customer = Customer_Factory::get_by_user_id( $user_id, false ) ) {
			$customer->delete();
		}
	}


	/**
	 * Returns true if a guest was converted.
	 *
	 * @param int $user_id
	 * @return bool
	 */
	static function maybe_update_guest_customer_when_user_registers( $user_id ) {
		$user = get_userdata( $user_id );

		if ( ! $user || ! $user->user_email ) {
			return false;
		}

		// if the guest and user have the same email address convert and delete them
		// we won't delete the guest record if the emails don't match, e.g. with a cookie matched guest
		if ( ! $guest = Guest_Factory::get_by_email( Clean::email( $user->user_email ) ) ) {
			return false;
		}

		self::convert_guest_to_registered_customer( $guest, $user );
		$guest->delete(); // clear all guest data (including cart)
		return true;
	}


	/**
	 * Convert guest customer to registered user customer.
	 *
	 * @param Guest $guest
	 * @param \WP_User $user
	 */
	static function convert_guest_to_registered_customer( $guest, $user ) {
		$guest_customer = Customer_Factory::get_by_guest_id( $guest->get_id(), false );

		if ( ! $guest_customer ) {
			return; // nothing to convert
		}

		$user_customer = Customer_Factory::get_by_user_id( $user->ID, false );

		if ( $user_customer ) {
			return; // user already exists, guest will just be deleted
		}

		// we have a guest customer that needs to be converted to a registered customer
		$guest_customer->set_guest_id( 0 );
		$guest_customer->set_user_id( $user->ID );
		$guest_customer->save();

		$guest_customer->clear_review_count_cache();

		do_action( 'automatewoo/customer/converted_guest_to_registered_customer', $guest_customer );
	}


	/**
	 * Dispatches background process to create customer records for all registered users.
	 * Runs in batches of 50 items every 30 seconds.
	 */
	static function setup_registered_customers() {

		/** @var Background_Processes\Setup_Registered_Customers $process */
		$process = Background_Processes::get('setup_registered_customers');

		if ( $process->has_queued_items() ) {
			$process->maybe_schedule_health_check();
			return; // already running
		}

		$limit = 50;

		$users = get_users([
			'fields' => 'ids',
			'number' => $limit,
			'meta_query' => [
				[
					'key' => '_automatewoo_customer_id',
					'compare' => 'NOT EXISTS'
				]
			]
		]);

		if ( $users ) {
			$process->data( $users )->start();
		}
		else {
			do_action( 'automatewoo_setup_guest_customers' );
		}
	}


	/**
	 * Dispatches background process to setup guest customers
	 * Goes through every guest order and creates a customer for it
	 * Runs in batches of 50 items every 30 seconds.
	 */
	static function maybe_setup_guest_customers() {

		if ( get_option( '_automatewoo_setup_guest_customers_complete' ) ) {
			return;
		}

		/** @var Background_Processes\Setup_Guest_Customers $process */
		$process = Background_Processes::get('setup_guest_customers');

		if ( $process->has_queued_items() ) {
			$process->maybe_schedule_health_check();
			return; // already running
		}

		$limit = 50;
		$offset = get_option( '_automatewoo_setup_guest_customers_offset', 0 );

		// guest orders
		$orders = wc_get_orders([
			'type' => 'shop_order',
			'limit' => $limit,
			'offset' => $offset,
			'status' => wc_get_is_paid_statuses(),
			'customer_id' => 0,
			'return' => 'ids'
		]);

		if ( $orders ) {
			$process->data( $orders )->start();
			update_option( '_automatewoo_setup_guest_customers_offset', $offset + $limit, false );
		}
		else {
			update_option( '_automatewoo_setup_guest_customers_complete', true, false );
			delete_option( '_automatewoo_setup_guest_customers_offset' );
		}

	}


	/**
	 * Update last order date after order processing or completed, used by customer win back trigger
	 *
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	static function maybe_update_customer_last_order_date( $order_id, $old_status, $new_status ) {

		if ( ! in_array( $new_status, apply_filters( 'automatewoo/customer/last_order_date_statuses', wc_get_is_paid_statuses() ) ) ) {
			return;
		}

		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( ! $customer = Customer_Factory::get_by_order( $order ) ) {
			return;
		}

		$customer->set_date_last_purchased( $order->get_date_paid() );
		$customer->save();
	}


	/**
	 * When any guest order changes status recache the most recent order prop.
	 *
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	static function maybe_update_guest_most_recent_order( $order_id, $old_status, $new_status ) {
		if ( ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		if ( $order->get_user_id() ) {
			return;
		}

		// get customer, also creates the guest if needed
		if ( $customer = Customer_Factory::get_by_order( $order ) ) {
			if ( $guest = $customer->get_guest() ) {
				$guest->recache_most_recent_order_id();
			}
		}
	}

	/**
	 * Clears persistent review count cache.
	 *
	 * @since 4.5
	 *
	 * @param int $comment_id
	 */
	static function clean_review_count_cache_on_clean_comment_cache( $comment_id ) {
		$review = Review_Factory::get( $comment_id );

		if ( ! $review ) {
			return;
		}

		$customer = $review->get_customer();

		if ( $customer ) {
			$customer->clear_review_count_cache();
		}
	}


}
