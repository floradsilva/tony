<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

use AutomateWoo\Subscription_Workflow_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * @class Subscription_Status
 */
class Subscription_Status extends Preloaded_Select_Rule_Abstract {

	public $data_item = 'subscription';


	function init() {
		parent::init();

		$this->title = __( 'Subscription - Status', 'automatewoo' );
	}


	function load_select_choices() {
		return Subscription_Workflow_Helper::get_subscription_statuses();
	}


	/**
	 * @param $subscription \WC_Subscription
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $subscription, $compare, $value ) {
		return $this->validate_select( 'wc-' . $subscription->get_status(), $compare, $value );
	}

}
