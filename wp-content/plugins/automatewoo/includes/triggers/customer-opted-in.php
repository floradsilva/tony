<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Customer_Opted_In
 */
class Trigger_Customer_Opted_In extends Trigger {

	public $supplied_data_items = [ 'customer' ];


	function load_admin_details() {
		$this->title = __( 'Customer Opted In', 'automatewoo' );
		$this->group = __( 'Customers', 'automatewoo' );
	}


	function register_hooks() {
		add_action( 'automatewoo/customer/opted_in', [ $this, 'opted_in' ] );
	}


	/**
	 * @param Customer $customer
	 */
	function opted_in( $customer ) {
		$this->maybe_run([
			'customer' => $customer
		]);
	}

}
