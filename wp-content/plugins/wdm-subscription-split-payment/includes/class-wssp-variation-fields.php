<?php
/**
 * WSSP Variation Fields
 *
 * Adds a Subscription setting tab and saves subscription settings. Adds a Subscriptions Management page. Adds
 * Welcome messages and pointers to streamline learning process for new users.
 *
 * @package     WooCommerce Subscriptions
 * @subpackage  WC_Subscriptions_Admin
 * @category    Class
 * @author      Brent Shepherd
 * @since       1.0
 */
class WSSP_Variation_Fields {

	/**
	 * Bootstraps the class and hooks required actions & filters.
	 *
	 * @since 1.0
	 */
	public static function init() {
		// And also on the variations section
		add_action( 'woocommerce_product_after_variable_attributes', __CLASS__ . '::variable_subscription_shipping_fields', 12, 3 );
		add_action( 'woocommerce_save_product_variation', __CLASS__ . '::save_product_variation', 20, 2 );
	}

	public static function variable_subscription_shipping_fields( $loop, $variation_data, $variation ) {
		$variation_product = wc_get_product( $variation );

		$billing_period = WC_Subscriptions_Product::get_period( $variation_product );

		if ( empty( $billing_period ) ) {
			$billing_period = 'month';
		}

		$shipping_interval = $variation_product->get_meta( '_subscription_shipping_interval', true );

		require WSSP_PLUGIN_PATH . 'templates/admin/html-variation-shipment.php';
	}


	/**
	 * Save meta info for subscription variations
	 *
	 * @param int $variation_id
	 * @param int $i
	 * return void
	 * @since 2.0
	 */
	public static function save_product_variation( $variation_id, $index ) {

		if ( ! WC_Subscriptions_Product::is_subscription( $variation_id ) || empty( $_POST['_wcsnonce_save_variations'] ) || ! wp_verify_nonce( $_POST['_wcsnonce_save_variations'], 'wcs_subscription_variations' ) ) {
			return;
		}

		if ( isset( $_POST['variable_subscription_shipping'][ $index ] ) ) {
			$subscription_shipping_interval = wc_format_decimal( $_POST['variable_subscription_shipping'][ $index ] );
			update_post_meta( $variation_id, '_subscription_shipping_interval', $subscription_shipping_interval );
		}
	}
}
