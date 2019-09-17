<?php
/**
 * Admin View: Notice - Update DB
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="error">
	<p>
	<?php
	// translators: HTML tags.
	printf( esc_html( __( '%1$sWooCommerce Price Based on Country Database Update Required%2$s We just need to update your install to the latest version', 'wc-price-based-country' ) ), '<strong>', '</strong> &#8211;' );
	?>
	</p>
	<p class="submit"><a href="<?php echo esc_url( add_query_arg( 'do_update_wc_price_based_country', 'true', admin_url( 'admin.php?page=wc-settings&tab=price-based-country' ) ) ); ?>" class="wc-update-now button-primary"><?php esc_html_e( 'Run the updater', 'wc-price-based-country' ); ?></a></p>
</div>
<script type="text/javascript">
	jQuery('.wc-update-now').click('click', function(){
		var answer = confirm( '<?php esc_html_e( 'It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'wc-price-based-country' ); ?>' );
		return answer;
	});
</script>
