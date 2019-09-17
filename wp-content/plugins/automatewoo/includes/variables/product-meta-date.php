<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Product_Meta_Date
 */
class Variable_Product_Meta_Date extends Variable_Order_Meta_Date {

	/**
	 * @param \WC_Product $product
	 * @param $parameters array
	 * @return string|bool
	 */
	function get_value( $product, $parameters ) {
		if ( ! $parameters['key'] ) {
			return false;
		}

		$value = Clean::string( Compat\Product::get_meta( $product, $parameters['key'] ) );
		return $this->format_datetime( $value, $parameters, true );
	}
}

return new Variable_Product_Meta_Date();
