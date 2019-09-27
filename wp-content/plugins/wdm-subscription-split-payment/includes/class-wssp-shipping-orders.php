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
		add_action( 'woocommerce_order_status_processing', __CLASS__ . '::on_processing_status_subscription_order' );
		add_filter( 'wcs_renewal_order_created', __CLASS__ . '::add_shipping_to_renewal_orders', 100, 2 );
	}

	/**
	 * Tasks to do when subscription is created.
	 */
	public static function on_subscription_creation( $subscription, $order, $recurring_cart ) {
		$items = $subscription->get_items( 'line_item' );

		foreach ( $items as $key => $item ) {
			$product = $item->get_product();

			$shipping_interval = $product->get_meta( '_subscription_shipping_interval' );

			$subscription->add_meta_data( '_wssp_shipping_interval', $shipping_interval );
			$subscription->add_meta_data( '_wssp_shipping_status', 0 );
			$order->add_meta_data( '_wssp_shipping_status_order', 0 );

			break;
		}

		$subscription->save();
		$order->save();
	}


	/**
	 * Tasks to do when the status of a subscription_order is in processing.
	 */
	public static function on_processing_status_subscription_order( $order_id ) {
		$order = wc_get_order( $order_id );

		$subscriptions = wcs_get_subscriptions_for_order( $order, array( 'order_type' => 'any' ) );

		foreach ( $subscriptions as $key => $value ) {
			$subscription = $value;
			break;
		}

		if ( $subscription ) {
			$subscription_period          = $subscription->get_billing_period();
			$subscription_period_interval = $subscription->get_billing_interval();

			$shipping_interval = $subscription->get_meta( '_wssp_shipping_interval' );
			$shipping_status   = (int) $subscription->get_meta( '_wssp_shipping_status' );

			if ( 0 !== $shipping_status ) {
				$order->update_status( 'completed', __( 'No Shipping Required.', 'wdm-subscription-split-payment' ) );
			}

			$shipping_status = ( $shipping_status + $subscription_period_interval ) % $shipping_interval;

			// check if shipping already updated
			$order_shipping_status = $order->get_meta( '_wssp_shipping_status_order' );
			if ( $order_shipping_status ) {
				return;
			}

			$subscription->update_meta_data( '_wssp_shipping_status', $shipping_status );
			$subscription->save();
			$order->add_meta_data( '_wssp_shipping_status', $shipping_status );
			$order->save();
		}
	}


	public static function get_next_shipping_date( $shipping_interval ) {
		$next_shipping_date         = new WC_DateTime( 'now', wc_timezone_string() );
		$shipping_interval_duration = new DateInterval( 'P' . $shipping_interval . 'M' );
		$next_shipping_date         = $next_shipping_date->add( $shipping_interval_duration );

		return $next_shipping_date;
	}


	public static function add_shipping_to_renewal_orders( $order, $subscription ) {
		$shipping_status = (int) $subscription->get_meta( '_wssp_shipping_status' );

		if ( 0 !== $shipping_status ) {
			$shipping_items = $order->get_shipping_methods();
			foreach ( $shipping_items as $key => $shipping_item ) {
				$shipping_id = $shipping_item->get_id();
				$order->remove_item( $shipping_id );
			}

			$shipping_total = $order->calculate_shipping();

			$total = $order->calculate_totals();
			$order->save();
		}

		return $order;
	}
}
