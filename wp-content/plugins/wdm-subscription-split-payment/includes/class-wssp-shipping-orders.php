<?php
/**
 * WSSP Shipping Information In Orders
 *
 * @category    Class
 * @since       1.0
 */
class WSSP_Shipping_Orders {

	/**
	 * Add shipping information in Orders
	 *
	 * @since 1.0
	 */

	public static function init() {
		add_action( 'woocommerce_checkout_subscription_created', __CLASS__ . '::on_subscription_creation', 10, 3 );
	}

	public static function on_subscription_creation( $subscription, $order, $recurring_cart ) {
		$items = $subscription->get_items( 'line_item' );

		foreach ( $items as $key => $item ) {
			$shipping_data = array();

			$product                            = $item->get_product();
			$product_id = $product->get_id();

			$shipping_interval = $product->get_meta( '_subscription_shipping_interval' );
			$subscription->add_meta_data( '_shipping_interval_' . $product_id, $shipping_interval );
		}

		$subscription->save();

		// $order->add_meta_data( 'subscription_interval', $shipping_interval );
		// $order->save();
	}
}
