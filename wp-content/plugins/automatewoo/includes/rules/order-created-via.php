<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Order_Created_Via
 */
class Order_Created_Via extends Preloaded_Select_Rule_Abstract {

	public $data_item = 'order';


	function init() {
		parent::init();

		$this->title = __( 'Order - Created Via', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return [
			'checkout' => __( 'Checkout', 'automatewoo' ),
			'rest-api' => __( 'REST API', 'automatewoo' ),
		];
	}


	/**
	 * @param \WC_Order $order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {
		return $this->validate_select( $order->get_created_via(), $compare, $value );
	}

}
