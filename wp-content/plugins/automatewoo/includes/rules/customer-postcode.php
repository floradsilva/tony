<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Customer_Postcode
 */
class Customer_Postcode extends Abstract_String {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer - Postcode', 'automatewoo' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_string( $this->data_layer()->get_customer_postcode(), $compare, $value );
	}

}
