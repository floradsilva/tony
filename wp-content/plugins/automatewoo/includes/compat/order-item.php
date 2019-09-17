<?php
// phpcs:ignoreFile

namespace AutomateWoo\Compat;

/**
 * @class Order_Item
 * @since 2.9
 */
class Order_Item {

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item $item
	 * @return int
	 */
	static function get_id( $item ) {
		return $item->get_id();
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item_Product $item
	 * @return int
	 */
	static function get_product_id( $item ) {
		return $item->get_product_id();
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item_Product $item
	 * @return int
	 */
	static function get_variation_id( $item ) {
		return $item->get_variation_id();
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item_Product $item
	 * @param \WC_Order $order
	 * @return \WC_Product
	 */
	static function get_product( $item, $order ) {
		return $item->get_product();
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item_Product $item
	 * @return int
	 */
	static function get_quantity( $item ) {
		return $item->get_quantity();
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item_Product $item
	 * @return string
	 */
	static function get_name( $item ) {
		return $item->get_name();
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item $item
	 * @param string $attribute slug
	 * @return false|string
	 */
	static function get_attribute( $item, $attribute ) {
		return $item->get_meta( $attribute );
	}

	/**
	 * @deprecated
	 *
	 * @param array|\WC_Order_Item $item
	 * @param string $attribute slug
	 * @return false|string
	 */
	static function get_meta( $item, $key ) {
		return $item->get_meta( $key );
	}


	/**
	 * This method is still required. WC separated the instance ID from the method ID starting in 3.4
	 * https://github.com/woocommerce/woocommerce/pull/18483
	 *
	 * @param array|\WC_Order_Item_Shipping $item
	 * @param bool $discard_instance_id
	 * @return false|string
	 */
	static function get_shipping_method_id( $item, $discard_instance_id = false ) {
		$id = $item->get_method_id();

		if ( $discard_instance_id ) {
			// extract method base id only, discard instance id
			if ( $split = strpos( $id, ':') ) {
				$id = substr( $id, 0, $split );
			}
		}

		return $id;

	}


}