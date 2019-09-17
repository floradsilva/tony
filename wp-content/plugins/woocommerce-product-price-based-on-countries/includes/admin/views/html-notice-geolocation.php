<?php
/**
 * Admin View: Notice - Geolocation
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="notice notice-warning notice-pbc pbc-is-dismissible">
	<a class="notice-dismiss notice-pbc-close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'geolocation' ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss' ); ?></a>
	<?php // Translators: HTML tags. ?>
	<p><strong>WooCommerce Price Based on Country:</strong> <?php printf( esc_html__( 'Geolocation is required. Set %1$sDefault customer location%2$s to %3$sGeolocate%4$s.', 'wc-price-based-country' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">', '</a>', '<strong><em>', '</em></strong>' ); ?></p>
</div>
