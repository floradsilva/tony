<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_Guest_Order_Count
 */
class AW_Rule_Guest_Order_Count extends AutomateWoo\Rules\Abstract_Number {

	public $data_item = 'guest';

	public $support_floats = false;


	/**
	 * Init
	 */
	function init() {
		$this->title = __( 'Guest - Order Count', 'automatewoo' );
	}


	/**
	 * @param $guest AutomateWoo\Guest
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $guest, $compare, $value ) {
		$customer = AutomateWoo\Customer_Factory::get_by_email( $guest->get_email() );
		return $this->validate_number( $customer->get_order_count(), $compare, $value );
	}

}
