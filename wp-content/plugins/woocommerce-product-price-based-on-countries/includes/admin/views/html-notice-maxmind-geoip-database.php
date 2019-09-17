<?php
/**
 * Admin View: Notice - MaxMind GeoIP database
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-info notice-pbc pbc-is-dismissible">
	<a class="notice-dismiss notice-pbc-close" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'pbc-hide-notice', 'maxmind_geoip_database' ), 'pbc_hide_notice_nonce' ) ); ?>"><?php esc_html_e( 'Dismiss' ); ?></a>
	<p><strong>WooCommerce Price Based on Country: </strong>
	<?php // Translators: HTML tags. ?>
	<?php printf( esc_html__( 'The MaxMind GeoIP Database does not exist, geolocation will not function. %1$sClick here to install the MaxMind GeoIP Database now%2$s.', 'wc-price-based-country' ), '<a href="' . esc_url( add_query_arg( 'wcpbc_update_geoip_database', wp_create_nonce( 'wcpbc-update-geoipdb' ) ) ) . '">', '</a>' ); ?></p>
</div>
