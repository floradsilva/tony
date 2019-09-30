<?php
/**
 * Cart Subscription Options Template.
 *
 * Override this template by copying it to 'yourtheme/woocommerce/cart/cart-subscription-options.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 2.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<h2><?php _e( 'Subscription Plans', 'woocommerce-all-products-for-subscriptions' ); ?></h2>
<p><?php _e( 'Subscribe to these items?', 'woocommerce-all-products-for-subscriptions' ); ?></p>
<ul class="wcsatt-options-cart">
	<?php
		foreach ( $options as $option ) {
			?>
				<li class="<?php echo esc_attr( $option[ 'class' ] ); ?>">
					<label>
						<input type="radio" name="convert_to_sub" value="<?php echo $option[ 'value' ] ?>" <?php checked( $option[ 'selected' ], true, true ); ?> />
						<?php echo '<span class="' . esc_attr( $option[ 'class' ] ) . '-details">' . wp_kses_post( $option[ 'description' ] ) . '</span>'; ?>
					</label>
				</li>
			<?php
		}
	?>
</ul>

