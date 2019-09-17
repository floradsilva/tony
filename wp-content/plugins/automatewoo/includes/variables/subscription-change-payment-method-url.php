<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Variable_Subscription_Change_Payment_Method_Url class.
 *
 * @since 4.4.3
 */
class Variable_Subscription_Change_Payment_Method_Url extends Variable {

	/**
	 * Load admin props.
	 */
	public function load_admin_details() {
		$this->description = __( 'Shows a URL to the subscription add/change payment method page.', 'automatewoo' );
	}

	/**
	 * Get the variable's value.
	 *
	 * @param \WC_Subscription $subscription
	 * @param array            $parameters
	 *
	 * @return string
	 */
	public function get_value( $subscription, $parameters ) {
		return $subscription->get_change_payment_method_url();
	}

}

return new Variable_Subscription_Change_Payment_Method_Url();
