<?php
// phpcs:ignoreFile

defined( 'ABSPATH' ) || exit;

/**
 * @class AW_Rule_Guest_Email
 */
class AW_Rule_Guest_Email extends AutomateWoo\Rules\Abstract_String {

	public $data_item = 'guest';


	function init() {
		$this->title = __( 'Guest - Email', 'automatewoo' );
	}


	/**
	 * @param $guest AutomateWoo\Guest
	 * @param $compare
	 * @param $value
	 * @return bool
	 */
	function validate( $guest, $compare, $value ) {
		return $this->validate_string( $guest->get_email(), $compare, $value );
	}

}
