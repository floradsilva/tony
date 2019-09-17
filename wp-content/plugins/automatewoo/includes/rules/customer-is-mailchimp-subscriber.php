<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

use AutomateWoo\Integrations;

defined( 'ABSPATH' ) || exit;

/**
 * @class Customer_Is_MailChimp_Subscriber
 */
class Customer_Is_MailChimp_Subscriber extends Abstract_Select_Single {

	public $data_item = 'customer';


	function init() {
		$this->title = __( 'Customer - Is Subscribed To MailChimp List', 'automatewoo' );
		$this->placeholder = __( 'Select a list&hellip;', 'automatewoo' );
	}


	/**
	 * @return array
	 */
	function get_select_choices() {
		return Integrations::mailchimp()->get_lists();
	}


	/**
	 * @param $customer \AutomateWoo\Customer
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $customer, $compare, $value ) {
		return Integrations::mailchimp()->is_contact( $customer->get_email(), $value );
	}

}
