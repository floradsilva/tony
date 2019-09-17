<?php

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * Subscription created date rule.
 *
 * @Class   Subscription_Created_Date
 * @package AutomateWoo\Rules
 */
class Subscription_Created_Date extends Abstract_Date {
	/**
	 * Data item type.
	 *
	 * @var string
	 */
	public $data_item = 'subscription';

	/**
	 * Subscription_Created_Date constructor.
	 */
	public function __construct() {
		$this->has_is_past_comparision = true;

		parent::__construct();
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->title = __( 'Subscription - Created Date', 'automatewoo' );
	}

	/**
	 * Validates rule.
	 *
	 * @param \WC_Subscription $subscription The subscription.
	 * @param string           $compare      What variables we're using to compare.
	 * @param array|null       $value        The values we have to compare. Null is only allowed when $compare is is_not_set.
	 *
	 * @return bool
	 */
	public function validate( $subscription, $compare, $value = null ) {
		return $this->validate_date( $compare, $value, aw_normalize_date( $subscription->get_date_created() ) );
	}
}
