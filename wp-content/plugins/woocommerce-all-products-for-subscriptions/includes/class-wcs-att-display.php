<?php
/**
 * WCS_ATT_Display class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce All Products For Subscriptions
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end support and single-product template modifications.
 *
 * @class    WCS_ATT_Display
 * @version  2.3.2
 */
class WCS_ATT_Display {

	/**
	 * Initialization.
	 */
	public static function init() {

		// Cart display hooks.
		require_once( 'display/class-wcs-att-display-cart.php' );
		// Single-product display hooks.
		require_once( 'display/class-wcs-att-display-product.php' );
		// Front-end ajax hooks.
		require_once( 'display/class-wcs-att-display-ajax.php' );

		self::add_hooks();
	}

	/**
	 * Hook-in.
	 */
	private static function add_hooks() {

		// Enqueue scripts and styles.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'frontend_scripts' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Filters
	|--------------------------------------------------------------------------
	*/

	/**
	 * Front end styles and scripts.
	 *
	 * @return void
	 */
	public static function frontend_scripts() {

		wp_register_style( 'wcsatt-css', WCS_ATT()->plugin_url() . '/assets/css/frontend/woocommerce.css', false, WCS_ATT::VERSION, 'all' );
		wp_style_add_data( 'wcsatt-css', 'rtl', 'replace' );
		wp_enqueue_style( 'wcsatt-css' );

		if ( is_cart() ) {

			wp_register_script( 'wcsatt-cart', WCS_ATT()->plugin_url() . '/assets/js/frontend/cart.js', array( 'jquery', 'wc-country-select', 'wc-address-i18n' ), WCS_ATT::VERSION, true );
			wp_enqueue_script( 'wcsatt-cart' );

			$params = array(
				'i18n_update_cart_sub_error' => __( 'Failed to update your cart. If this issue persists, please re-load the page and try again.', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_subs_load_error'       => __( 'Failed to load matching subscriptions. If this issue persists, please re-load the page and try again.', 'woocommerce-all-products-for-subscriptions' ),
				'update_cart_option_nonce'   => wp_create_nonce( 'wcsatt_update_cart_option' ),
				'wc_ajax_url'                => WC_AJAX::get_endpoint( "%%endpoint%%" )
			);

			wp_localize_script( 'wcsatt-cart', 'wcsatt_cart_params', $params );
		}

		wp_register_script( 'wcsatt-single-product', WCS_ATT()->plugin_url() . '/assets/js/frontend/single-add-to-cart.js', array( 'jquery', 'jquery-blockui', 'backbone' ), WCS_ATT::VERSION, true );

		$params = array(
			'i18n_subs_load_error' => __( 'Failed to load matching subscriptions. If this issue persists, please re-load the page and try again.', 'woocommerce-all-products-for-subscriptions' ),
			'wc_ajax_url'          => WC_AJAX::get_endpoint( "%%endpoint%%" )
		);

		wp_localize_script( 'wcsatt-single-product', 'wcsatt_single_product_params', $params );
	}

	/*
	|--------------------------------------------------------------------------
	| Deprecated
	|--------------------------------------------------------------------------
	*/

	/**
	 * Options for purchasing a product once or creating a subscription from it.
	 *
	 * @param  WC_Product  $product
	 * @return void
	 */
	public static function get_subscription_options_content( $product ) {
		_deprecated_function( __METHOD__ . '()', '2.0.0', 'WCS_ATT_Display_Product::get_subscription_options_content()' );
		return WCS_ATT_Display_Product::get_subscription_options_content( $product );
	}
}

WCS_ATT_Display::init();
