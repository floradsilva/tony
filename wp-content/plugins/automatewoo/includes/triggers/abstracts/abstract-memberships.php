<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Trigger_Abstract_Memberships
 * @since 2.8.3
 */
abstract class Trigger_Abstract_Memberships extends Trigger {

	public $supplied_data_items = [ 'membership', 'customer' ];


	function load_admin_details() {
		$this->group = __( 'Memberships', 'automatewoo' );
	}


	/**
	 * @return Fields\Select
	 */
	function get_field_membership_plans() {

		$plans = Memberships_Helper::get_membership_plans();

		return ( new Fields\Select() )
			->set_name( 'membership_plans' )
			->set_title( __( 'Membership plans', 'automatewoo'  ) )
			->set_placeholder( __( 'Select which membership plans to trigger for. Leave blank to apply for all plans.', 'automatewoo'  ) )
			->set_options( $plans )
			->set_multiple();
	}

}
