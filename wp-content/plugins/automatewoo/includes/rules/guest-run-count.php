<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_Guest_Run_Count
 */
class AW_Rule_Guest_Run_Count extends AutomateWoo\Rules\Abstract_Number {

	public $data_item = 'guest';

	public $support_floats = false;


	function init() {
		$this->title = __( "Workflow - Run Count For Guest", 'automatewoo' );
	}


	/**
	 * @param $guest AutomateWoo\Guest
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $guest, $compare, $value ) {

		if ( ! $workflow = $this->get_workflow() )
			return false;

		return $this->validate_number( $workflow->get_times_run_for_guest( $guest ), $compare, $value );
	}

}
