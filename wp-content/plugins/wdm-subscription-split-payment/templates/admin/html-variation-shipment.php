<?php
/**
 * Outputs a subscription variation's pricing fields for WooCommerce 2.3+
 *
 * @version 2.2.12
 *
 * @var int $loop
 * @var WSSP_Variation_Fields $variation_product
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="variable_subscription_pricing variable_subscription_pricing_2_3 show_if_variable-subscription">
	<p class="form-row form-row-first form-field show_if_variable-subscription _subscription_price_field">
		<label for="variable_subscription_shipping[<?php echo esc_attr( $loop ); ?>]">
			<?php
			// translators: placeholder is a currency symbol / code
			echo __( 'Shipping Interval in Months', 'wdm-subscription-split-payment' );
			?>
		</label>
		<input type="number" class="wc_input_price wc_input_subscription_price" name="variable_subscription_shipping[<?php echo esc_attr( $loop ); ?>]" value="<?php echo $shipping_interval; ?>" placeholder="<?php echo __( 'e.g. 1', 'wdm-subscription-split-payment' ); ?>
		" min="1">    
	</p>
</div>
