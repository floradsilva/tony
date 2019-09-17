<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Action to add a chosen coupon code to a subscription.
 *
 * @class Action_Subscription_Add_Coupon
 * @since 4.4
 */
class Action_Subscription_Add_Coupon extends Action_Subscription_Edit_Coupon_Abstract {


	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Add Coupon', 'automatewoo' );
		$this->description = __( 'Add a coupon to discount future payments for a subscription. The coupon will be added using the discount amount set on the coupon. This action can be used for bulk editing subscriptions, or to change the coupons provided to a subscriber at different stages of their subscription\'s lifecycle. The same coupon code will only be added once to a subscription. Only recurring coupon types can be added.', 'automatewoo' );
	}


	/**
	 * Add a given coupon as a line item to a given subscription.
	 *
	 * @param \WC_Coupon       $coupon Coupon to add to the subscription.
	 * @param \WC_Subscription $subscription Instance of subscription to add the coupon to.
	 *
	 * @throws \Exception When there is an error.
	 */
	protected function edit_subscription( $coupon, $subscription ) {
		$response = $subscription->apply_coupon( $coupon );

		if ( is_wp_error( $response ) ) {
			throw new \Exception( $response->get_error_message() );
		}
	}


	/**
	 * Get a message to add to the subscription to record the coupon being added by this action.
	 *
	 * Helpful for tracing the history of this action by viewing the subscription's notes.
	 *
	 * @param \WC_Coupon $coupon Coupon being added to the subscription. Required so its name can be added to the order note.
	 * @return string
	 */
	protected function get_note( $coupon ) {
		return sprintf( __( '%1$s workflow run: added coupon %2$s to subscription. (Workflow ID: %3$d)', 'automatewoo' ), $this->workflow->get_title(), $coupon->get_code(), $this->workflow->get_id() );
	}

	/**
	 * Get the codes of all recurring coupons, as only these can be added to subscriptions.
	 *
	 * @return array Coupon codes (as both key and value of array)
	 */
	protected function get_coupons_list() {

		$coupon_codes = parent::get_coupons_list();

		foreach ( $coupon_codes as $code ) {

			$coupon = new \WC_Coupon( $code );

			if ( ! in_array( $coupon->get_discount_type(), array( 'recurring_fee', 'recurring_percent' ), true ) ) {
				unset( $coupon_codes[ $code ] );
			}
		}

		return $coupon_codes;
	}
}
