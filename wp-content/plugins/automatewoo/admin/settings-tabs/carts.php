<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Settings_Tab_Carts
 */
class Settings_Tab_Carts extends Admin_Settings_Tab_Abstract {


	function __construct() {
		$this->id = 'carts';
		$this->name = __( 'Carts', 'automatewoo' );
	}


	function load_settings() {

		$this->section_start( 'carts' );

		$this->add_setting( 'abandoned_cart_enabled', [
			'type' => 'checkbox',
			'title' => __( 'Enable cart tracking', 'automatewoo' ),
			'autoload' => true
		]);

		$this->add_setting( 'abandoned_cart_timeout', [
			'type' => 'number',
			'title' => __( 'Abandoned cart timeout', 'automatewoo' ),
			'desc_tip' => __( 'Set the number of minutes from when a cart is last active to when it is considered abandoned. Default value is 15.', 'automatewoo' ),
		]);

		$this->add_setting( 'abandoned_cart_includes_pending_orders', [
			'type' => 'checkbox',
			'title' => __( 'Include failed, cancelled and pending orders as abandoned carts', 'automatewoo' ),
			'desc_tip' => __( 'When disabled, carts that are converted to pending, cancelled or failed orders will not be included in abandoned cart workflows.', 'automatewoo' ),
			'autoload' => true
		]);

		$this->add_setting( 'clear_inactive_carts_after', [
			'type' => 'number',
			'title' => __( 'Clear inactive carts after', 'automatewoo' ),
			'desc_tip' => __( 'Set the days after which an inactive cart will be deleted. Default value is 60.', 'automatewoo' ),
		]);

		$this->section_end( 'carts' );
	}

}

return new Settings_Tab_Carts();
