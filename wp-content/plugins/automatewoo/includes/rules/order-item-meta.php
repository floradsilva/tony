<?php
// phpcs:ignoreFile

namespace AutomateWoo\Rules;

defined( 'ABSPATH' ) || exit;

/**
 * @class Order_Item_Meta
 */
class Order_Item_Meta extends Abstract_Meta {

	public $data_item = 'order_item';


	function init() {
		$this->title = __( 'Order Line Item - Custom Field', 'automatewoo' );
	}


	/**
	 * @param \WC_Order_Item_Product $order_item
	 * @param $compare_type
	 * @param $value_data
	 *
	 * @return bool
	 */
	function validate( $order_item, $compare_type, $value_data ) {

		$value_data = $this->prepare_value_data( $value_data );

		if ( ! is_array( $value_data ) ) {
			return false;
		}

		return $this->validate_meta( wc_get_order_item_meta( $order_item->get_id(), $value_data['key'] ), $compare_type, $value_data['value'] );
	}
}
