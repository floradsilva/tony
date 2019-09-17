<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Order_Billing_Country
 */
class Order_Billing_Country extends Preloaded_Select_Rule_Abstract {

	public $data_item = 'order';


	function init() {
		parent::init();

		$this->title = __( 'Order - Billing Country', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return WC()->countries->get_allowed_countries();
	}


	/**
	 * @param $order \WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( $order->get_billing_country(), $compare, $value );
	}

}
