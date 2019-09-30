<?php
/**
 * WCS_ATT_Integration_PAO class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce All Products For Subscriptions
 * @since    2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Compatibility with Product Add-Ons.
 *
 * @class    WCS_ATT_Integration_PAO
 * @version  2.3.0
 */
class WCS_ATT_Integration_PAO {

	/**
	 * Initialize.
	 */
	public static function init() {
		self::add_hooks();
	}

	/**
	 * Hooks for PAO support.
	 */
	private static function add_hooks() {

		// Add price data to one-time option.
		add_filter( 'wcsatt_single_product_one_time_option_data', array( __CLASS__, 'maybe_add_one_time_option_price_data' ), 10, 3 );

		// Add price data to subscription options.
		add_filter( 'wcsatt_single_product_subscription_option_data', array( __CLASS__, 'maybe_add_subscription_option_price_data' ), 10, 4 );
	}

	/*
	|--------------------------------------------------------------------------
	| Helpers
	|--------------------------------------------------------------------------
	*/

	/**
	 * Used to tell if a product has (required) addons.
	 *
	 * @since  2.3.0
	 *
	 * @param  mixed    $product
	 * @param  boolean  $required
	 * @return boolean
	 */
	public static function has_addons( $product, $required = false ) {

		if ( is_object( $product ) && is_a( $product, 'WC_Product' ) ) {
			$product_id = $product->get_id();
		} else {
			$product_id = absint( $product );
		}

		$has_addons = false;
		$cache_key  = 'product_addons_' . $product_id;

		$addons = WCS_ATT_Helpers::cache_get( $cache_key );

		if ( is_null( $addons ) ) {
			$addons = WC_Product_Addons_Helper::get_product_addons( $product_id, false, false );
			WCS_ATT_Helpers::cache_set( $cache_key, $addons );
		}

		if ( ! empty( $addons ) ) {

			if ( $required ) {

				foreach ( $addons as $addon ) {

					$type = ! empty( $addon[ 'type' ] ) ? $addon[ 'type' ] : '';

					if ( 'heading' !== $type && isset( $addon[ 'required' ] ) && '1' == $addon[ 'required' ] ) {
						$has_addons = true;
						break;
					}
				}

			} else {
				$has_addons = true;
			}
		}

		return $has_addons;
	}

	/*
	|--------------------------------------------------------------------------
	| Hooks - Application
	|--------------------------------------------------------------------------
	*/

	/**
	 * Add price data to one-time option.
	 *
	 * @param  array       $data
	 * @param  WC_Product  $product
	 * @return array
	 */
	public static function maybe_add_one_time_option_price_data( $data, $product, $parent_product ) {
		return self::maybe_add_option_price_data( $data, false, $product, $parent_product );
	}

	/**
	 * Add price data to subscription options.
	 *
	 * @param  array       $data
	 * @param  WC_Product  $product
	 * @return array
	 */
	public static function maybe_add_subscription_option_price_data( $data, $scheme, $product, $parent_product ) {
		return self::maybe_add_option_price_data( $data, $scheme->get_key(), $product, $parent_product );
	}

	/**
	 * Add price data to SATT options.
	 *
	 * @param  array       $data
	 * @param  WC_Product  $product
	 * @return array
	 */
	public static function maybe_add_option_price_data( $data, $scheme_key, $product, $parent_product ) {

		if ( ! WCS_ATT_Product_Schemes::price_filter_exists( WCS_ATT_Product_Schemes::get_subscription_schemes( $product ) ) ) {
			return $data;
		}

		if ( ! self::has_addons( $parent_product ? $parent_product : $product ) ) {
			return $data;
		}

		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		$raw_price        = WCS_ATT_Product_Prices::get_price( $product, $scheme_key );
		$display_price    = 'incl' === $tax_display_mode ? wc_get_price_including_tax( $product, array( 'price' => $raw_price ) ) : wc_get_price_excluding_tax( $product, array( 'price' => $raw_price ) );

		$data[ 'raw_price' ]     = $raw_price;
		$data[ 'display_price' ] = $display_price;

		return $data;
	}
}
