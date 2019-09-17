<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_User_Run_Count
 */
class AW_Rule_Order_Run_Count extends AutomateWoo\Rules\Abstract_Number {

	public $data_item = 'order';

	public $support_floats = false;


	function init() {
		$this->title = __( "Workflow - Run Count For Order", 'automatewoo' );
	}


	/**
	 * @param WC_Order $order
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $order, $compare, $value ) {

		if ( ! $workflow = $this->get_workflow() )
			return false;

		return $this->validate_number( $workflow->get_run_count_for_order( $order ), $compare, $value );
	}

}
