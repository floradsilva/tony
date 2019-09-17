<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Memberships_Delete_User_Membership
 * @since 2.9
 */
class Action_Memberships_Delete_User_Membership extends Action_Memberships_Abstract {

	public $required_data_items = [ 'customer' ];


	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( "Delete Membership For User", 'automatewoo' );
	}


	function load_fields() {

		$plans = Memberships_Helper::get_membership_plans();

		$plan = ( new Fields\Select( false ) )
			->set_name( 'plan' )
			->set_title( __( 'Plan', 'automatewoo' ) )
			->set_options( $plans )
			->set_required();

		$this->add_field( $plan );

	}


	/**
	 * @throws \Exception
	 */
	function run() {

		$customer = $this->workflow->data_layer()->get_customer();
		$plan_id = absint( $this->get_option( 'plan' ) );

		if ( ! $customer->is_registered() || ! $plan_id ) {
			return;
		}

		$membership = wc_memberships_get_user_membership( $customer->get_user_id(), $plan_id );

		if ( ! $membership ) {
			$this->workflow->log_action_note( $this, __( 'The user did not have membership that matched the selected plan.', 'automatewoo' ) );
			return;
		}

		$membership_id = $membership->get_id();

		$success = wp_delete_post( $membership_id, true );

		if ( $success ) {
			$this->workflow->log_action_note( $this, sprintf( __( 'Deleted membership #%s', 'automatewoo' ), $membership_id ) );
		}
		else {
			throw new \Exception( sprintf( __( 'Failed deleting membership #%s', 'automatewoo' ), $membership_id ) );
		}
	}

}
