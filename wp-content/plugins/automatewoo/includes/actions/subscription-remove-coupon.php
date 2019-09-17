<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Action to remove all line items matching a chosen coupons ID from a workflow's subscription.
 *
 * @class Action_Subscription_Remove_Coupon
 * @since 4.4
 */
class Action_Subscription_Remove_Coupon extends Action_Subscription_Edit_Coupon_Abstract {


	/**
	 * Explain to store admin what this action does via a unique title and description.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Remove Coupon', 'automatewoo' );
		$this->description = __( 'Remove a coupon line item or items from a subscription, if any line items match the chosen coupon code. This is useful for bulk editing subscriptions, or to change the coupons provided to a subscriber at different stages of their subscription\'s lifecycle. Please note: all line items that match the chosen coupon code will be removed.', 'automatewoo' );
	}


	/**
	 * Remove all line items for a coupon that have a code matching a given coupon.
	 *
	 * More than one line item may be removed if more than one line item matches the given
	 * coupon's code.
	 *
	 * @param \WC_Coupon       $coupon Coupon to removed from the subscription.
	 * @param \WC_Subscription $subscription Instance of subscription to remove the coupon from.
	 */
	protected function edit_subscription( $coupon, $subscription ) {

		foreach ( $subscription->get_items( 'coupon' ) as $item ) {
			if ( $item->get_code() === $coupon->get_code() ) {
				$subscription->remove_item( $item->get_id() );
			}
		}

		// updates totals and saves subscription
		$subscription->calculate_totals();
	}


	/**
	 * Create a note recording the coupon name and workflow name to add after removing coupons.
	 *
	 * @param \WC_Coupon $coupon Coupon being removed from the subscription. Required so its name can be added to the order note.
	 * @return string
	 */
	protected function get_note( $coupon ) {
		return sprintf( __( '%1$s workflow run: removed coupon %2$s. (Workflow ID: %3$d)', 'automatewoo' ), $this->workflow->get_title(), $coupon->get_code(), $this->workflow->get_id() );
	}
}
