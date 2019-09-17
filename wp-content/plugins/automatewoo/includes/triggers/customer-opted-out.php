<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Customer_Opted_Out
 */
class Trigger_Customer_Opted_Out extends Trigger {

	public $supplied_data_items = [ 'customer' ];


	function load_admin_details() {
		$this->title = __( 'Customer Opted Out', 'automatewoo' );
		$this->description = __( 'Fires when a customer unsubscribes from workflows.', 'automatewoo' );
		$this->group = __( 'Customers', 'automatewoo' );
	}


	function register_hooks() {
		add_action( 'automatewoo/customer/opted_out', [ $this, 'opted_out' ] );
	}


	/**
	 * @param Customer $customer
	 */
	function opted_out( $customer ) {
		$this->maybe_run([
			'customer' => $customer
		]);
	}

}
