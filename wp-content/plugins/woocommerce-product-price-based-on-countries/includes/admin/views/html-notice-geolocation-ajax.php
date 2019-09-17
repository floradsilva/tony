<?php
/**
 * Admin View: Notice - Geolocation Ajax
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-warning notice-pbc">
	<p><strong>WooCommerce Price Based on Country: </strong>
	<?php // Translators: HTML tags. ?>
	<?php printf( esc_html__( 'You enabled Caching Support option of Price Based on Country. Set %1$sDefault customer location%2$s to %3$sGeolocate%4$s.', 'wc-price-based-country' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings' ) ) . '">', '</a>', '<strong><em>', '</em></strong>' ); ?></p>
</div>
