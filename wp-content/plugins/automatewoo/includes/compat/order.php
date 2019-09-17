<?php
// phpcs:ignoreFile

namespace AutomateWoo\Compat;

use AutomateWoo\Format;
use AutomateWoo\DateTime;

/**
 * @class Order
 * @since 2.9
 *
 * @deprecated
 */
class Order {

	/**
	 * @param \WC_Order $order
	 *
	 * @return int
	 */
	static function get_id( $order ) {
		return is_callable( [ $order, 'get_id' ] ) ? $order->get_id() : $order->id;
	}

	/**
	 * Returns mysql format
	 *
	 * @param \WC_Order $order
	 * @param bool $gmt
	 *
	 * @return string
	 */
	static function get_date_created( $order, $gmt = false ) {
		$date = $order->get_date_created() ? $order->get_date_created()->date( Format::MYSQL ) : false;

		if ( $gmt && $date ) {
			return get_gmt_from_date( $date, Format::MYSQL );
		}

		return $date;
	}

	/**
	 * @param \WC_Order $order
	 * @param DateTime $date
	 */
	static function set_date_created( $order, $date ) {
		$order->set_date_created( $date->getTimestamp() );
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	static function get_customer_ip( $order ) {
		return $order->get_customer_ip_address();
	}

	/**
	 * @param \WC_Order $order
	 * @param $key
	 *
	 * @return mixed
	 */
	static function get_meta( $order, $key ) {
		return $order->get_meta( $key );
	}

	/**
	 * @param \WC_Order $order
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	static function update_meta( $order, $key, $value ) {
		$order->update_meta_data( $key, $value );
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 * @param $key
	 * @return mixed
	 */
	static function delete_meta( $order, $key ) {
		$order->delete_meta_data( $key );
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 * @param $value
	 */
	static function set_customer_id( $order, $value ) {
		$order->set_customer_id( $value );
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 * @param $value
	 */
	static function set_billing_email( $order, $value ) {
		$order->set_billing_email( $value );
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_email( $order ) {
		return $order->get_billing_email();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_first_name( $order ) {
		return $order->get_billing_first_name();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_last_name( $order ) {
		return $order->get_billing_last_name();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_company( $order ) {
		return $order->get_billing_company();
	}


	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_phone( $order ) {
		return $order->get_billing_phone();
	}


	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_country( $order ) {
		return $order->get_billing_country();
	}


	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_address_1( $order ) {
		return $order->get_billing_address_1();
	}


	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_address_2( $order ) {
		return $order->get_billing_address_2();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_city( $order ) {
		return $order->get_billing_city();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_state( $order ) {
		return $order->get_billing_state();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_billing_postcode( $order ) {
		return $order->get_billing_postcode();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_shipping_country( $order ) {
		return $order->get_shipping_country();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_shipping_address_1( $order ) {
		return $order->get_shipping_address_1();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_shipping_address_2( $order ) {
		return $order->get_shipping_address_2();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_shipping_city( $order ) {
		return $order->get_shipping_city();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_shipping_state( $order ) {
		return $order->get_shipping_state();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_shipping_postcode( $order ) {
		return $order->get_shipping_postcode();
	}

	/**
	 * @param \WC_Order $order
	 * @param \WC_Order_Item_Product|array $item
	 * @return \WC_Product
	 */
	static function get_product_from_item( $order, $item ) {
		return $item->get_product();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_order_key( $order ) {
		return $order->get_order_key();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_payment_method( $order ) {
		return $order->get_payment_method();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_payment_method_title( $order ) {
		return $order->get_payment_method_title();
	}

	/**
	 * @param \WC_Order $order
	 * @param $note
	 */
	static function set_customer_note( $order, $note ) {
		$order->set_customer_note( $note );
		$order->save();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_customer_note( $order ) {
		return $order->get_customer_note();
	}

	/**
	 * @param \WC_Order $order
	 * @return string
	 */
	static function get_created_via( $order ) {
		return $order->get_created_via();
	}

	/**
	 * @param \WC_Order $order
	 * @param $value
	 * @return string
	 */
	static function set_created_via( $order, $value ) {
		$order->set_created_via( $value );
		$order->save();
	}

	/**
	 * @return array
	 */
	static function get_paid_statuses() {
		return wc_get_is_paid_statuses();
	}

	/**
	 * @param \WC_Order $order
	 * @since 4.2
	 * @return float
	 */
	static function get_shipping_total( $order ) {
		return (float) $order->get_shipping_total();
	}

	/**
	 * @param \WC_Order $order
	 * @since 4.2
	 * @return float
	 */
	static function get_shipping_tax( $order ) {
		return (float) $order->get_shipping_tax();
	}

	/**
	 * @param \WC_Order $order
	 * @since 4.2
	 * @return float
	 */
	static function get_discount_total( $order ) {
		return (float) $order->get_discount_total();
	}

	/**
	 * @param \WC_Order $order
	 * @since 4.2
	 * @return float
	 */
	static function get_discount_tax( $order ) {
		return (float) $order->get_discount_tax();
	}

}
