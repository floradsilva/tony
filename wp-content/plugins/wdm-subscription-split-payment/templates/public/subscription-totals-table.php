<?php
/**
 * Subscription details table
 *
 * @author  Prospress
 * @package WooCommerce_Subscription/Templates
 * @since 2.6.0
 * @version 2.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>
<table class="shop_table order_details">
	<thead>
		<tr>
			<?php if ( $allow_item_removal ) : ?>
			<th class="product-remove" style="width: 3em;">&nbsp;</th>
			<?php endif; ?>
			<th class="product-name"><?php echo esc_html_x( 'Product', 'table headings in notification email', 'woocommerce-subscriptions' ); ?></th>
			<th class="product-total"><?php echo esc_html_x( 'Total', 'table heading', 'woocommerce-subscriptions' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $subscription->get_items() as $item_id => $item ) {
			$_product  = apply_filters( 'woocommerce_subscriptions_order_item_product', $subscription->get_product_from_item( $item ), $item );
			if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
				?>
				<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $subscription ) ); ?>">
					<?php if ( $allow_item_removal ) : ?>
						<td class="remove_item">
							<?php if ( wcs_can_item_be_removed( $item, $subscription ) ) : ?>
								<?php $confirm_notice = apply_filters( 'woocommerce_subscriptions_order_item_remove_confirmation_text', __( 'Are you sure you want remove this item from your subscription?', 'woocommerce-subscriptions' ), $item, $_product, $subscription );?>
								<a href="<?php echo esc_url( WCS_Remove_Item::get_remove_url( $subscription->get_id(), $item_id ) );?>" class="remove" onclick="return confirm('<?php printf( esc_html( $confirm_notice ) ); ?>');">&times;</a>
							<?php endif; ?>
						</td>
					<?php endif; ?>
					<td class="product-name">
						<?php
						if ( $_product && ! $_product->is_visible() ) {
							echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item['name'], $item, false ) );
						} else {
							echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', sprintf( '<a href="%s">%s</a>', get_permalink( $item['product_id'] ), $item['name'] ), $item, false ) );
						}

						echo wp_kses_post( apply_filters( 'woocommerce_order_item_quantity_html', ' <strong class="product-quantity">' . sprintf( '&times; %s', $item['qty'] ) . '</strong>', $item ) );

						/**
						 * Allow other plugins to add additional product information here.
						 *
						 * @param int $item_id The subscription line item ID.
						 * @param WC_Order_Item|array $item The subscription line item.
						 * @param WC_Subscription $subscription The subscription.
						 * @param bool $plain_text Wether the item meta is being generated in a plain text context.
						 */
						do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $subscription, false );

						wcs_display_item_meta( $item, $subscription );

						/**
						 * Allow other plugins to add additional product information here.
						 *
						 * @param int $item_id The subscription line item ID.
						 * @param WC_Order_Item|array $item The subscription line item.
						 * @param WC_Subscription $subscription The subscription.
						 * @param bool $plain_text Wether the item meta is being generated in a plain text context.
						 */
						do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $subscription, false );
						?>
					</td>
					<td class="product-total">
						<?php echo wp_kses_post( $subscription->get_formatted_line_subtotal( $item ) ); ?>
					</td>
				</tr>
				<?php
			}

			if ( $subscription->has_status( array( 'completed', 'processing' ) ) && ( $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) ) {
				?>
				<tr class="product-purchase-note">
					<td colspan="3"><?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?></td>
				</tr>
				<?php
			}
		}
		?>
	</tbody>
	<tfoot>
        <?php
            $wssp_subscription_total      = wcs_get_objects_property( $subscription, 'wssp_subscription_total' );
            $shipping_interval            = $subscription->get_meta( '_wssp_shipping_interval' );            
        ?>

        <tr>
            <th scope="row" <?php echo ( $allow_item_removal ) ? 'colspan="2"' : ''; ?>><?php echo esc_html_e( 'Total Number of Installments', 'wdm-subscription-split-payment' ); ?></th>
            <td><?php echo wp_kses_post( $shipping_interval ); ?></td>
        </tr>

        <?php if ( isset( $wssp_subscription_total ) ) :?>
            <tr>
                <th scope="row" <?php echo ( $allow_item_removal ) ? 'colspan="2"' : ''; ?>><?php echo esc_html_e( 'Total Product Price', 'wdm-subscription-split-payment' ); ?></th>
                <td><?php echo wc_price( $wssp_subscription_total, array( 'currency' => $subscription->get_currency() ) ); ?></td>
            </tr>
        <?php endif; ?>
	</tfoot>
</table>
