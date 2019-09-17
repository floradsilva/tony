<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Variable_Product_Permalink
 */
class Variable_Product_Permalink extends Variable {


	function load_admin_details() {
		$this->description = __( "Displays the permalink to the product.", 'automatewoo');
	}


	/**
	 * @param $product \WC_Product
	 * @param $parameters
	 * @return string
	 */
	function get_value( $product, $parameters ) {
		return $product->get_permalink();
	}
}

return new Variable_Product_Permalink();

