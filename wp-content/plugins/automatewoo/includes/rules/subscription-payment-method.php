<?php

namespace AutomateWoo\Rules;

use AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Subscription_Payment_Method
 *
 * @package AutomateWoo\Rules
 */
class Subscription_Payment_Method extends AutomateWoo\Rule_Order_Payment_Gateway {

	/**
	 * Define the data type used by the rule.
	 *
	 * @var string
	 */
	public $data_item = 'subscription';

	/**
	 * Init rule.
	 */
	public function init() {
		parent::init();
		$this->title = __( 'Subscription - Payment Method', 'automatewoo' );
	}

	/**
	 * Load rule select choices.
	 *
	 * @return array
	 */
	public function load_select_choices() {
		$choices = [];

		foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $gateway ) {
			if ( $gateway->supports( 'subscriptions' ) ) {
				$choices[ $gateway->id ] = $gateway->get_title();
			}
		}

		return $choices;
	}

}
