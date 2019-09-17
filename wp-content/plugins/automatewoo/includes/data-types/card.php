<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Data_Type_Card
 * @since 3.7
 */
class Data_Type_Card extends Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'WC_Payment_Token_CC');
	}


	/**
	 * @param \WC_Payment_Token_CC $item
	 * @return mixed
	 */
	function compress( $item ) {
		return $item->get_id();
	}


	/**
	 * @param $compressed_item
	 * @param $compressed_data_layer
	 * @return \WC_Payment_Token_CC|\WC_Payment_Token|false
	 */
	function decompress( $compressed_item, $compressed_data_layer ) {
		if ( ! $compressed_item ) {
			return false;
		}
		return \WC_Payment_Tokens::get( absint( $compressed_item ) );
	}

}

return new Data_Type_Card();
