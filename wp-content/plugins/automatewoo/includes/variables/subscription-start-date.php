<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Subscription_Start_Date
 */
class Variable_Subscription_Start_Date extends Variable_Abstract_Datetime {


	function load_admin_details() {
		parent::load_admin_details();
		$this->description = __( "Displays the subscription start date in your website's timezone.", 'automatewoo' );
		$this->description .= ' ' . $this->_desc_format_tip;
	}


	/**
	 * @param $subscription \WC_Subscription
	 * @param $parameters
	 * @return string
	 */
	function get_value( $subscription, $parameters ) {
		return $this->format_datetime( $subscription->get_date_created(), $parameters );
	}
}

return new Variable_Subscription_Start_Date();
