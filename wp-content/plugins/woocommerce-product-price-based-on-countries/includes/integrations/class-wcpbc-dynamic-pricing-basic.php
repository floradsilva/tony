<?php
/**
 * Integration with WooCommerce Dynamic Pricing.
 *
 * @package WCPBC
 * @version 1.8.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WCPBC_Dynamic_Pricing_Basic' ) ) {
	exit;
}

/**
 * WCPBC_Dynamic_Pricing_Basic Class
 */
class WCPBC_Dynamic_Pricing_Basic {

	/**
	 * Hook actions and filters
	 *
	 * @since 1.7.2
	 */
	public static function init() {
		add_filter( 'wc_dynamic_pricing_get_product_pricing_rule_sets', array( __CLASS__, 'get_product_pricing_rules' ), 10, 3 );
	}

	/**
	 * Retrun pricing_rules after apply the exchange rate
	 *
	 * @param mixed                               $pricing_rules Pricing rules.
	 * @param int                                 $post_id Post ID.
	 * @param WC_Dynamic_Pricing_Advanced_Product $product Product Instance.
	 * @return array
	 */
	public static function get_product_pricing_rules( $pricing_rules, $post_id, $product ) {
		if ( ! empty( $pricing_rules ) ) {

			foreach ( $pricing_rules as $rule_key => $rule ) {
				if ( empty( $rule['rules'] ) ) {
					continue;
				}

				foreach ( $rule['rules'] as $i => $value ) {
					if ( in_array( $value['type'], array( 'fixed_price', 'price_discount' ), true ) ) {
						$pricing_rules[ $rule_key ]['rules'][ $i ]['amount'] = wcpbc_the_zone()->get_exchange_rate_price( $value['amount'] );
					}
				}
			}
		}

		return $pricing_rules;
	}

}
add_action( 'wc_price_based_country_frontend_princing_init', array( 'WCPBC_Dynamic_Pricing_Basic', 'init' ) );

