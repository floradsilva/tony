<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_Order_Shipping_Country
 */
class AW_Rule_Order_Shipping_Country extends AutomateWoo\Rules\Preloaded_Select_Rule_Abstract {

	public $data_item = 'order';


	function init() {
		parent::init();

		$this->title = __( 'Order - Shipping Country', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return WC()->countries->get_allowed_countries();
	}


	/**
	 * @param $order WC_Order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( $order->get_shipping_country(), $compare, $value );
	}

}
