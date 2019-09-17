<?php
/**
 * Admin View: Notice - Unable to install GeoIP database
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$database    = is_callable( array( 'WC_Geolocation', 'get_local_database_path' ) ) ? WC_Geolocation::get_local_database_path() : '';
$geolite_url = defined( 'WC_Geolocation::GEOLITE2_DB' ) ? WC_Geolocation::GEOLITE2_DB : WC_Geolocation::GEOLITE_DB;
?>

<div class="notice notice-error is-dismissible">
	<p><strong>WooCommerce Price Based on Country: </strong>
	<?php // Translators: HTML tags, database path. ?>
	<?php printf( esc_html__( 'Unable to install the GeoIP database. You can %1$sdownload it from maxmind.com%2$s and upload it manually to %3$s. %4$sRead more%2$s.', 'wc-price-based-country' ), '<a href="' . esc_url( $geolite_url ) . '">', '</a>', '<code>' . esc_html( $database ) . '</code>', '<a target="_blank" rel="noopener noreferrer" href="https://www.pricebasedcountry.com/docs/common-issues/the-maxmind-geoip-database-does-not-exist/">' ); ?></p>
</div>
