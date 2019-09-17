<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Order_Coupons_Text_Match
 */
class Order_Coupons_Text_Match extends Abstract_String {

	/** @var array  */
	public $data_item = 'order';


	function init() {
		$this->title = __( 'Order - Coupons - Text Match', 'automatewoo' );
		$this->compare_types = $this->get_multi_string_compare_types();
	}


	/**
	 * @param \WC_Order $order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		$coupons = is_callable( [ $order, 'get_coupon_codes' ] ) ? $order->get_coupon_codes() : $order->get_used_coupons();
		return $this->validate_string_multi( $coupons, $compare, $value );
	}

}
