<?php
/**
 * Admin View: Page - Status Report.
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $wpdb;

?>
<table class="wc_status_table widefat" cellspacing="0">
<thead>
		<tr>
			<th colspan="3" data-export-label="Geolocation debug info"><h2><?php esc_html_e( 'Geolocation debug info', 'wc-price-based-country' ); ?></h2></th>
		</tr>
	</thead>
	<tbody id="wcpbc-geolocation-debug">
		<?php
		foreach ( array( 'HTTP_CF_IPCOUNTRY', 'GEOIP_COUNTRY_CODE', 'HTTP_X_COUNTRY_CODE', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' ) as $server_var ) :
			$server_var_value = isset( $_SERVER[ $server_var ] ) ? wcpbc_sanitize_server_var( $_SERVER[ $server_var ] ) : false; // WPCS: sanitization ok, CSRF ok.
			$server_var_value = 'HTTP_X_FORWARDED_FOR' === $server_var && $server_var_value ? rest_is_ip_address( trim( current( preg_split( '/,/', $server_var_value ) ) ) ) : $server_var_value;
		?>
		<tr id="wcpbc-<?php echo esc_html( str_replace( '_', '-', strtolower( $server_var ) ) ); ?>" data-value="<?php echo esc_html( false !== $server_var_value ? $server_var_value : '' ); ?>">
			<td data-export-label="<?php echo esc_attr( $server_var ); ?>"><?php echo esc_html( $server_var ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo ( empty( $_SERVER[ $server_var ] ) ? '<mark class="no dashicons dashicons-no-alt"></mark>' : esc_html( $server_var_value ) ); ?></td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td data-export-label="Real external IP"><?php esc_html_e( 'Real external IP' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td id="wcpbc-real-external-ip"></td>
			<?php
				wc_enqueue_js("
				function show_real_ip(ip){
					$('#wcpbc-real-external-ip').text(ip.trim());
					$('#wcpbc-real-external-ip').data('value', ip.trim());
					$( '#wcpbc-geolocation-debug' ).trigger( 'wc_price_based_country_real_external_ip_loaded' );
				}
				$.get('https://icanhazip.com/', show_real_ip)
				.fail(function(){
					$.get('https://ident.me/', show_real_ip);
				});");
			?>
		</tr>
		<tr id="wcpbc-use-remote-addr" data-value="<?php echo defined( 'WCPBC_USE_REMOTE_ADDR' ) && WCPBC_USE_REMOTE_ADDR ? '1' : ''; ?>">
			<td data-export-label="WCPBC_USE_REMOTE_ADDR">Const WCPBC_USE_REMOTE_ADDR:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo defined( 'WCPBC_USE_REMOTE_ADDR' ) && WCPBC_USE_REMOTE_ADDR ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr id="wcpbc-geolocation-test">
			<td data-export-label="Geolocation Test"><?php esc_html_e( 'Geolocation Test', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td class="wcpbc-geolocation-test-result"><?php esc_html_e( 'Runing', 'wc-price-based-country' ); ?>...</td>
		</tr>
	</tbody>
</table>

<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="PBC Settings"><h2>Price Based on Country <?php esc_html_e( 'General options', 'wc-price-based-country' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td data-export-label="Version"><?php esc_html_e( 'Version', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( wcpbc()->version ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Base location"><?php esc_html_e( 'Base location', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_default_country' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Base currency"><?php esc_html_e( 'Base currency', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( wcpbc_get_base_currency() ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Price Based On"><?php esc_html_e( 'Price Based On', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'wc_price_based_country_based_on', 'billing' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Shipping"><?php esc_html_e( 'Shipping', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'wc_price_based_country_shipping_exchange_rate', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Test mode"><?php esc_html_e( 'Test mode', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'wc_price_based_country_test_mode', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Test country"><?php esc_html_e( 'Test country', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo wp_kses_post( 'yes' === get_option( 'wc_price_based_country_test_mode', 'no' ) ? get_option( 'wc_price_based_country_test_country', '<mark class="no">&ndash;</mark>' ) : '<mark class="no">&ndash;</mark>' ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Load products price in background"><?php esc_html_e( 'Load products price in background', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'wc_price_based_country_caching_support', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Prices entered with tax"><?php esc_html_e( 'Prices entered with tax', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo 'yes' === get_option( 'woocommerce_prices_include_tax', 'no' ) ? '<mark class="yes"><span class="dashicons dashicons-yes"></span></mark>' : '<mark class="no">&ndash;</mark>'; ?></td>
		</tr>
		<tr>
			<td data-export-label="Calculate tax based on"><?php esc_html_e( 'Calculate tax based on', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_tax_based_on' ) ); ?></td>
		</tr>
		<tr>
			<td data-export-label="Display prices in the shop"><?php esc_html_e( 'Display prices in the shop', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'woocommerce_tax_display_shop' ) ); ?></td>
		</tr>
		<?php if ( wcpbc_is_pro() ) : ?>
		<tr>
			<td data-export-label="Currency format"><?php esc_html_e( 'Currency Format', 'wc-price-based-country' ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( get_option( 'wc_price_based_currency_format' ) ); ?></td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>
<?php foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) : ?>
<table class="wc_status_table widefat" cellspacing="0">
	<thead>
		<tr>
			<th colspan="3" data-export-label="Zone Pricing <?php echo esc_html( $zone->get_name() ); ?>"><h2><?php echo esc_html( __( 'Zone Pricing', 'wc-price-based-country' ) . ': "' . $zone->get_name() . '"' ); ?></h2></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach ( $zone->get_data() as $key => $value ) : ?>
		<tr>
			<td data-export-label="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $key ); ?>:</td>
			<td class="help">&nbsp;</td>
			<td><?php echo esc_html( is_array( $value ) ? implode( ' | ', $value ) : $value ); ?></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php endforeach; ?>
