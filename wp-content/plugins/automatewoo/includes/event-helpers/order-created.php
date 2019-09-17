<?php
// phpcs:ignoreFile

namespace AutomateWoo\Event_Helpers;

use AutomateWoo\Events;
use AutomateWoo\Clean;

/**
 * @class Order_Created
 */
class Order_Created {


	static function init() {
		add_action( 'woocommerce_new_order', [ __CLASS__, 'order_created' ], 100 );
		add_action( 'woocommerce_api_create_order', [ __CLASS__, 'order_created' ], 100 );
		add_action( 'woocommerce_checkout_order_processed', [ __CLASS__, 'order_created' ], 100 );
		add_filter( 'wcs_renewal_order_created', [ __CLASS__, 'filter_renewal_orders' ], 100 );

		if ( is_admin() ) {
			add_action( 'transition_post_status', [ __CLASS__, 'transition_post_status' ], 50, 3 );
		}

		add_action( 'automatewoo/async/maybe_order_created', [ __CLASS__, 'maybe_do_order_created_action' ] );
	}


	/**
	 * @param \WC_Order $order
	 * @return \WC_Order
	 */
	static function filter_renewal_orders( $order ) {
		self::order_created( $order->get_id() );
		return $order;
	}


	/**
	 * @param $new_status
	 * @param $old_status
	 * @param \WP_Post $post
	 */
	static function transition_post_status( $new_status, $old_status, $post ) {
		if ( $post->post_type !== 'shop_order' ) {
			return;
		}

		$draft_statuses = aw_get_draft_post_statuses();

		// because WP has multiple draft status, ensure that the old status IS a draft status and
		// the new status IS NOT a draft status
		if ( in_array( $old_status, $draft_statuses, true ) && ! in_array( $new_status, $draft_statuses, true ) ) {
			self::order_created( $post->ID );
		}
	}

	/**
	 * @param $order_id int
	 */
	static function order_created( $order_id ) {
		if ( ! $order_id || ! $order = wc_get_order( $order_id ) ) {
			return;
		}

		// note this event could be scheduled multiple times which is ok
		// because before the event runs a check happens to prevent multiple runs
		// we do check this async rather than now to avoid plugin conflicts
		Events::schedule_async_event( 'automatewoo/async/maybe_order_created', [ $order_id ], true );
	}


	/**
	 * Handles async order created event.
	 *
	 * Prevents duplicate events from running with a meta check.
	 *
	 * @param int $order_id
	 */
	static function maybe_do_order_created_action( $order_id ) {
		if ( ! $order_id || ! $order = wc_get_order( Clean::id( $order_id ) ) ) {
			return;
		}

		if ( $order->get_meta( '_automatewoo_order_created' ) ) {
			return;
		}

		$order->update_meta_data( '_automatewoo_order_created', true );
		$order->save();

		do_action( 'automatewoo/async/order_created', $order_id ); // run actual event
	}

}
