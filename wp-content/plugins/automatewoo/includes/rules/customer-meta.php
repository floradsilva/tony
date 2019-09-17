<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Customer_Meta
 */
class Customer_Meta extends Abstract_Meta {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer - Custom Field', 'automatewoo' );
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value_data
	 * @return bool
	 */
	function validate( $customer, $compare, $value_data ) {

		$value_data = $this->prepare_value_data( $value_data );

		if ( ! is_array( $value_data ) ) {
			return false;
		}

		return $this->validate_meta( $customer->get_legacy_meta( $value_data['key'] ), $compare, $value_data['value'] );

	}

}
