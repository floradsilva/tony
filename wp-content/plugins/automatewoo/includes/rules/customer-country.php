<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Customer_Country
 */
class Customer_Country extends Preloaded_Select_Rule_Abstract {

	public $data_item = 'customer';


	function init() {
		parent::init();

		$this->title = __( 'Customer - Country', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function load_select_choices() {
		return WC()->countries->get_allowed_countries();
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return $this->validate_select( $this->data_layer()->get_customer_country(), $compare, $value );
	}

}
