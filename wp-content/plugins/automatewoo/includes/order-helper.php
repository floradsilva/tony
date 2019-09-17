<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Order_Helper
 */
class Order_Helper {


	function __construct() {
		if ( AUTOMATEWOO_DISABLE_ASYNC_ORDER_STATUS_CHANGED ) {
			// if not using async status change hook refresh customer totals before triggers fire
			add_action( 'woocommerce_order_status_changed', [ $this, 'maybe_refresh_customer_totals' ], 5, 3 );
		}

		add_action( 'woocommerce_delete_shop_order_transients', [ $this, 'delete_shop_order_transients' ] );
	}


	/**
	 * @deprecated not needed post wc 3.0
	 *
	 * @param int $order_item_id
	 * @param array|\WC_Order_Item_Product $order_item
	 * @return array|bool|\WC_Order_Item_Product
	 */
	function prepare_order_item( $order_item_id, $order_item ) {

		if ( ! $order_item ) {
			return false;
		}

		// add id key for WC < 3.0
		if ( is_array( $order_item ) ) {
			$order_item['id'] = $order_item_id;
		}

		return $order_item;
	}


	/**
	 * In WC_Abstract_Order::update_status() customer totals refresh after change status hooks have fired.
	 * We need access to these for order triggers so manually refresh early.
	 * In the future order triggers could fire async which should solve this issue
	 *
	 * @param $order_id
	 * @param $old_status
	 * @param $new_status
	 */
	function maybe_refresh_customer_totals( $order_id, $old_status, $new_status ) {

		if ( ! in_array( $new_status, [ 'completed', 'processing', 'on-hold', 'cancelled' ] ) )
			return;

		if ( ! $order = wc_get_order( $order_id ) )
			return;

		$customer = Customer_Factory::get_by_order( $order );

		if ( $customer ) {
			$customer->delete_meta( 'order_count' );
			$customer->delete_meta( 'total_spent' );
		}

		$user_id = $order->get_user_id();

		if ( $user_id ) {
			delete_user_meta( $user_id, '_money_spent' );
			delete_user_meta( $user_id, '_order_count' );
			delete_user_meta( $user_id, '_aw_order_count' );
			delete_user_meta( $user_id, '_aw_order_ids' );
		}
	}


	/**
	 * @param $order_id
	 */
	function delete_shop_order_transients( $order_id ) {

		if ( ! $order = wc_get_order( $order_id ) )
			return;

		$customer = Customer_Factory::get_by_order( $order );

		if ( $customer ) {
			$customer->delete_meta( 'order_count' );
			$customer->delete_meta( 'total_spent' );
		}

		$user_id = $order->get_user_id();

		if ( $user_id ) {
			delete_user_meta( $user_id, '_aw_order_count' );
			delete_user_meta( $user_id, '_aw_order_ids' );
		}
	}


	/**
	 * LEGACY - use Customer object instead of this function
	 *
	 * @param \WC_Order $order
	 * @return Order_Guest|\WP_User|false
	 */
	function prepare_user_data_item( $order ) {

		if ( ! $order ) {
			return false;
		}

		$user = $order->get_user();

		if ( $user ) {
			// ensure first and last name are set
			if ( ! $user->first_name ) $user->first_name = Compat\Order::get_billing_first_name( $order );
			if ( ! $user->last_name ) $user->last_name = Compat\Order::get_billing_last_name( $order );
			if ( ! $user->billing_phone ) $user->billing_phone = Compat\Order::get_billing_phone( $order );
		}
		else {
			// order placed by a guest
			$user = new Order_Guest( $order );
		}

		return $user;
	}

}
