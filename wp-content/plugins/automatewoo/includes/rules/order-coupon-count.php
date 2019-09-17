<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Order_Coupon_Count
 * @since 4.2
 */
class Order_Coupon_Count extends Abstract_Number {

	public $data_item = 'order';

	public $support_floats = false;


	function init() {
		$this->title = __( 'Order - Coupon Count', 'automatewoo' );
	}


	/**
	 * @param $order \WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		$coupons = is_callable( [ $order, 'get_coupon_codes' ] ) ? $order->get_coupon_codes() : $order->get_used_coupons();
		return $this->validate_number( count( $coupons ), $compare, $value );
	}


}
