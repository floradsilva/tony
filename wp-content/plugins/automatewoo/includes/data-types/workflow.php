<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Data_Type_Workflow
 */
class Data_Type_Workflow extends Data_Type {

	/**
	 * @param $item
	 * @return bool
	 */
	function validate( $item ) {
		return is_a( $item, 'AutomateWoo\Workflow' );
	}


	/**
	 * @param Workflow $item
	 * @return mixed
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
		$workflow = Workflow_Factory::get( $compressed_item );

		if ( ! $workflow || $workflow->get_status() === 'trash' ) {
			return false;
		}

		return $workflow;
	}

}

return new Data_Type_Workflow();
