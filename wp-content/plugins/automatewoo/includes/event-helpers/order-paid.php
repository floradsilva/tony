<?php

namespace AutomateWoo\Event_Helpers;

use AutomateWoo\Clean;

/**
 * Event to fire when an order is first paid, supports payments by invoice, cheque, bank etc
 *
 * @class Order_Paid
 * @since 3.2.2
 */
class Order_Paid {

	/**
	 * Init Order Paid event helper.
	 */
	public static function init() {
		add_action( 'automatewoo/order/status_changed_async', [ __CLASS__, 'handle_async_order_status_changed' ], 10, 3 );
	}

	/**
	 * Determines whether the status change means the order is now paid.
	 *
	 * If the order is paid an action is triggered. This action can only run once for each order.
	 *
	 * @param int    $order_id
	 * @param string $old_status
	 * @param string $new_status
	 */
	public static function handle_async_order_status_changed( $order_id, $old_status, $new_status ) {
		if ( in_array( $old_status, wc_get_is_paid_statuses(), true ) ) {
			return;
		}

		if ( ! in_array( $new_status, wc_get_is_paid_statuses(), true ) ) {
			return;
		}

		$order = wc_get_order( Clean::id( $order_id ) );

		if ( ! $order || $order->get_meta( '_aw_is_paid' ) ) {
			return;
		}

		$order->update_meta_data( '_aw_is_paid', true );
		$order->save();

		do_action( 'automatewoo/order/paid_async', $order->get_id() );

		// This hook is also asynchronous, avoid using due to possible confusion
		do_action( 'automatewoo/order/paid', $order );
	}

}
