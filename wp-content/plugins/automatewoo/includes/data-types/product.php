<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Data_Type_Product
 */
class Data_Type_Product extends Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'WC_Product' );
	}


	/**
	 * @param \WC_Product $item
	 *
	 * @return int
	 */
	function compress( $item ) {
		return $item->get_id();
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return mixed
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		if ( ! $compressed_item ) {
			return false;
		}

		return wc_get_product( $compressed_item );
	}

}

return new Data_Type_Product();
