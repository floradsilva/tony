<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Subscription_Has_Payment_Method rule class.
 *
 * @since 4.4.3
 */
class Subscription_Has_Payment_Method extends Abstract_Bool {

	/**
	 * Data item for the rule.
	 *
	 * @var string
	 */
	public $data_item = 'subscription';

	/**
	 * Init the rule.
	 */
	public function init() {
		$this->title = __( 'Subscription - Has Payment Method', 'automatewoo' );
	}

	/**
	 * Validate the rule.
	 *
	 * @param \WC_Subscription $subscription
	 * @param string           $compare
	 * @param string           $value
	 *
	 * @return bool
	 */
	public function validate( $subscription, $compare, $value ) {
		$has = $subscription->has_payment_gateway();
		return $value === 'yes' ? $has : ! $has;
	}

}
