<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Variable_Order_Shipping_City class.
 *
 * @since 4.4.2
 *
 * @class Variable_Order_Shipping_City
 */
class Variable_Order_Shipping_City extends Variable {

	/**
	 * Load description for variable in admin screen.
	 */
	public function load_admin_details() {
		$this->description = __( 'Displays the shipping city for the order.', 'automatewoo' );
	}


	/** Method: get_value() - returns shipping city.
	 *
	 * @param \WC_Order $order
	 *
	 * @return string
	 */
	public function get_value( $order ) {
		return $order->get_shipping_city();
	}
}

return new Variable_Order_Shipping_City();
