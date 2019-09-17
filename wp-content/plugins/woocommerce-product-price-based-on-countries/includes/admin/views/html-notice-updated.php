<?php
/**
 * Admin View: Notice - Data Updated
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-success notice-pbc pbc-is-dismissible">
	<a class="notice-dismiss notice-pbc-close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'updated', remove_query_arg( 'do_update_wc_price_based_country' ) ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss' ); ?></a>
	<p><strong>WooCommerce Price Based on Country:</strong> <?php esc_html_e( 'Data update complete. Thank you for updating to the latest version!', 'wc-price-based-country' ); ?></p>
</div>
