<?php
// phpcs:ignoreFile

namespace AutomateWoo\Compat;

/**
 * @class Product
 * @since 2.9
 *
 * @deprecated
 */
class Product {

	/**
	 * @param \WC_Product|\WC_Product_Variation $product
	 * @return int
	 */
	static function get_id( $product ) {
		return $product->get_id();
	}

	/**
	 * @param \WC_Product $product
	 * @return int
	 */
	static function get_parent_id( $product ) {
		return $product->get_parent_id();
	}

	/**
	 * @param \WC_Product $product
	 * @return int
	 */
	static function get_name( $product ) {
		return $product->get_name();
	}

	/**
	 * @param \WC_Product $product
	 * @return bool
	 */
	static function is_variation( $product ) {
		return $product->is_type( [ 'variation', 'subscription_variation' ] );
	}

	/**
	 * @param \WC_Product $product
	 * @param $key
	 * @return mixed
	 */
	static function get_meta( $product, $key ) {
		return $product->get_meta( $key );
	}

	/**
	 * @param \WC_Product $product
	 * @param $key
	 * @return mixed
	 */
	static function get_parent_meta( $product, $key ) {
		$parent = wc_get_product( $product->get_parent_id() );
		return $parent ? $parent->get_meta( $key ) : false;
	}

	/**
	 * @param \WC_Product $product
	 * @param $key
	 * @param $value
	 * @return mixed
	 */
	static function update_meta( $product, $key, $value ) {
		$product->update_meta_data( $key, $value );
		$product->save();
	}

	/**
	 * @param \WC_Product $product
	 * @return array
	 */
	static function get_cross_sell_ids( $product ) {
		return $product->get_cross_sell_ids();
	}

	/**
	 * @param \WC_Product $product
	 * @return array
	 */
	static function get_price_including_tax( $product ) {
		return wc_get_price_including_tax( $product );
	}

	/**
	 * @param \WC_Product $product
	 * @return mixed
	 */
	static function get_description( $product ) {
		return $product->get_description();
	}

	/**
	 * @param \WC_Product $product
	 * @return string
	 */
	static function get_short_description( $product ) {
		return $product->get_short_description();
	}

	/**
	 * $product_id MUST NOT be a variation ID
	 *
	 * @param int $product_id
	 * @return array
	 */
	static function get_related( $product_id, $limit = 5 ) {
		return wc_get_related_products( $product_id, $limit );
	}

}
