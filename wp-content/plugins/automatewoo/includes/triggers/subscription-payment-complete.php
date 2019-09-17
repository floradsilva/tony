<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Subscription_Payment_Complete
 */
class Trigger_Subscription_Payment_Complete extends Trigger {

	/**
	 * Sets supplied data for the trigger.
	 *
	 * @var array
	 */
	public $supplied_data_items = [ 'customer', 'subscription', 'order' ];


	function load_admin_details() {
		$this->title = __( 'Subscription Renewal Payment Complete', 'automatewoo' );
		$this->group = Subscription_Workflow_Helper::get_group_name();
	}


	function load_fields() {
		$this->add_field( Subscription_Workflow_Helper::get_products_field() );
		$this->add_field( Subscription_Workflow_Helper::get_active_subscriptions_only_field() );
	}


	function register_hooks() {
		add_action( 'automatewoo/subscription/renewal_payment_complete_async', [ $this, 'handle_payment_complete' ], 10, 2 );
	}


	/**
	 * @param int $subscription_id
	 * @param int $order_id
	 */
	function handle_payment_complete( $subscription_id, $order_id ) {
		$subscription = wcs_get_subscription( $subscription_id );
		$order = wc_get_order( $order_id );

		if ( ! $subscription || ! $order ) {
			return;
		}

		$this->maybe_run([
			'subscription' => $subscription,
			'order' => $order,
			'customer' => Customer_Factory::get_by_user_id( $subscription->get_user_id() )
		]);
	}


	/**
	 * @param Workflow $workflow
	 * @return bool
	 */
	function validate_workflow( $workflow ) {

		$subscription = $workflow->data_layer()->get_subscription();

		if ( ! $subscription ) {
			return false;
		}

		if ( ! Subscription_Workflow_Helper::validate_products_field( $workflow ) ) {
			return false;
		}

		return true;
	}


	/**
	 * @param Workflow $workflow
	 * @return bool
	 */
	function validate_before_queued_event( $workflow ) {
		$subscription = $workflow->data_layer()->get_subscription();

		if ( ! $subscription ) {
			return false;
		}

		if ( ! Subscription_Workflow_Helper::validate_active_subscriptions_only_field( $workflow ) ) {
			return false;
		}

		return true;
	}
}
