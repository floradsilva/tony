<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Subscription trial end date rule.
 *
 * @class Subscription_Trial_End_Date
 */
class Subscription_Trial_End_Date extends Abstract_Date {

	/**
	 * Data item.
	 *
	 * @var string
	 */
	public $data_item = 'subscription';

	/**
	 * Subscription_Trial_End_Date constructor.
	 */
	public function __construct() {
		$this->has_is_future_comparision = true;
		$this->has_is_past_comparision   = true;

		parent::__construct();
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->title = __( 'Subscription - Trial End Date', 'automatewoo' );
	}


	/**
	 * Validates our rule.
	 *
	 * @param \WC_Subscription $subscription The subscription object.
	 * @param string           $compare      Rule to compare.
	 * @param array|null       $value        The values we have to compare. Null is only allowed when $compare is is_not_set.
	 *
	 * @return bool
	 */
	public function validate( $subscription, $compare, $value = null ) {
		return $this->validate_date( $compare, $value, aw_normalize_date( $subscription->get_date( 'trial_end' ) ) );
	}

}
