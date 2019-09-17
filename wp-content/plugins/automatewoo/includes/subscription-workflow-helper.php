<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Subscription_Workflow_Helper
 *
 * @since 4.5
 *
 * @package AutomateWoo
 */
class Subscription_Workflow_Helper {

	/**
	 * Get the subscription group name.
	 *
	 * @return string
	 */
	public static function get_group_name() {
		return __( 'Subscriptions', 'automatewoo' );
	}

	/**
	 * Get the subscription products field.
	 *
	 * @return Fields\Product
	 */
	public static function get_products_field() {
		$field = new Fields\Product();
		$field->set_name( 'subscription_products' );
		$field->set_title( __( 'Subscription products', 'automatewoo' ) );
		$field->set_description( __( 'Select products here to make this workflow only run on subscriptions with matching products. Leave blank to run for all products.', 'automatewoo' ) );
		$field->set_multiple( true );
		$field->set_allow_variations( true );

		return $field;
	}

	/**
	 * Get the 'active subscriptions only' field.
	 *
	 * @return Fields\Checkbox
	 */
	public static function get_active_subscriptions_only_field() {
		$field = new Fields\Checkbox();
		$field->set_name( 'active_only' );
		$field->set_title( __( 'Active subscriptions only', 'automatewoo' ) );
		$field->set_description( __( 'Enable to ensure the subscription is still active when the workflow runs. This is useful if the workflow is not run immediately.', 'automatewoo' ) );
		$field->default_to_checked = true;

		return $field;
	}

	/**
	 * Validate the subscription products field for a workflow.
	 *
	 * @param Workflow $workflow
	 *
	 * @return bool
	 */
	public static function validate_products_field( $workflow ) {
		$subscription          = $workflow->data_layer()->get_subscription();
		$subscription_products = $workflow->get_trigger_option( 'subscription_products' );

		// there's no product restriction
		if ( empty( $subscription_products ) ) {
			return true;
		}

		$included_product_ids = [];

		foreach ( $subscription->get_items() as $item ) {
			$included_product_ids[] = $item->get_product_id();
			$included_product_ids[] = $item->get_variation_id();
		}

		return (bool) array_intersect( $included_product_ids, $subscription_products );
	}


	/**
	 * Validate the 'active subscriptions only' field.
	 *
	 * @param Workflow $workflow
	 *
	 * @return bool
	 */
	public static function validate_active_subscriptions_only_field( $workflow ) {
		$subscription = $workflow->data_layer()->get_subscription();

		if ( $workflow->get_trigger_option( 'active_only' ) ) {
			if ( ! $subscription->has_status( 'active' ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Trigger for subscription.
	 *
	 * @param Trigger              $trigger
	 * @param int|\WC_Subscription $subscription
	 */
	public static function trigger_for_subscription( $trigger, $subscription ) {
		$subscription = wcs_get_subscription( $subscription );

		if ( ! $subscription ) {
			return;
		}

		$trigger->maybe_run(
			[
				'subscription' => $subscription,
				'customer'     => Customer_Factory::get_by_user_id( $subscription->get_user_id() ),
			]
		);
	}

	/**
	 * Trigger for each line item in a subscription.
	 *
	 * @param Trigger              $trigger
	 * @param int|\WC_Subscription $subscription
	 */
	public static function trigger_for_each_subscription_line_item( $trigger, $subscription ) {
		$subscription = wcs_get_subscription( $subscription );

		if ( ! $subscription ) {
			return;
		}

		$customer = Customer_Factory::get_by_user_id( $subscription->get_user_id() );

		foreach ( $subscription->get_items() as $order_item_id => $order_item ) {
			$trigger->maybe_run(
				[
					'subscription' => $subscription,
					'customer'     => $customer,
					'product'      => $order_item->get_product(),
				]
			);
		}
	}

	/**
	 * Get subscription statuses.
	 *
	 * Excludes the 'wc-switched' status.
	 *
	 * @since 4.5.0
	 *
	 * @return array
	 */
	public static function get_subscription_statuses() {
		$statuses = wcs_get_subscription_statuses();
		unset( $statuses['wc-switched'] );
		return $statuses;
	}

}
