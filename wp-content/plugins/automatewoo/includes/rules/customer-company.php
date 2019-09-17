<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Customer_Company
 */
class Customer_Company extends Abstract_String {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer - Company', 'automatewoo' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_string( $this->data_layer()->get_customer_company(), $compare, $value );
	}

}
