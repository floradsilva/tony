<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Subscription_Meta
 */
class Subscription_Meta extends Abstract_Meta {

	public $data_item = 'subscription';


	function init() {
		$this->title = __( 'Subscription - Custom Field', 'automatewoo' );
	}


	/**
	 * @param $subscription \WC_Subscription
	 * @param $compare_type
	 * @param $value_data
	 *
	 * @return bool
	 */
	function validate( $subscription, $compare_type, $value_data ) {

		$value_data = $this->prepare_value_data( $value_data );

		if ( ! is_array( $value_data ) ) {
			return false;
		}

		return $this->validate_meta( $subscription->get_meta( $value_data['key'] ), $compare_type, $value_data['value'] );
	}
}
