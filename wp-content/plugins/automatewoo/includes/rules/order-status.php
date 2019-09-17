<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Order_Status
 */
class Order_Status extends Preloaded_Select_Rule_Abstract {

	public $data_item = 'order';


	function init() {
		parent::init();

		$this->title = __( 'Order - Status', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return wc_get_order_statuses();
	}


	/**
	 * @param \WC_Order $order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( 'wc-' . $order->get_status(), $compare, $value );
	}

}
