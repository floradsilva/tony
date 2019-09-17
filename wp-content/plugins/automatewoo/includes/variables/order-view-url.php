<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Order_View_Url
 */
class Variable_Order_View_Url extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays a URL to view the order in the user account area.", 'automatewoo');
	}


	/**
	 * @param $order \WC_Order
	 * @param $parameters array
	 * @return string
	 */
	function get_value( $order, $parameters ) {
		return $order->get_view_order_url();
	}
}

return new Variable_Order_View_Url();
