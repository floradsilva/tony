<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Abstract_Shipment_Tracking
 *
 * @deprecated Use \AutomateWoo\Shipment_Tracking_Integration::get_shipment_tracking_field instead.
 */
abstract class Variable_Abstract_Shipment_Tracking extends Variable {

	/**
	 * Gets the first shipment tracking array
	 *
	 * @param $order \WC_Order
	 * @param $field
	 * @return false|string
	 */
	function get_shipment_tracking_field( $order, $field ) {
		return Shipment_Tracking_Integration::get_shipment_tracking_field( $order, $field );
	}
}
