<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Products items list
 *
 * Override this template by copying it to yourtheme/automatewoo/email/product-rows.php
 *
 * @see https://automatewoo.com/docs/email/product-display-templates/
 *
 * @var \WC_Product[] $products
 * @var Workflow $workflow
 * @var string $variable_name
 * @var string $data_type
 * @var string $data_field
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<?php if ( is_array( $products ) ): ?>

	<table cellspacing="0" cellpadding="0" style="width: 100%;" class="aw-product-rows"><tbody>

		<?php foreach ( $products as $product ): ?>
			<tr>

				<td class="image" width="25%">
					<a href="<?php echo $product->get_permalink() ?>"><?php echo \AW_Mailer_API::get_product_image( $product ) ?></a>
				</td>

				<td>
					<h3><a href="<?php echo $product->get_permalink() ?>"><?php echo Compat\Product::get_name( $product ); ?></a></h3>
				</td>

				<td align="right" class="last" width="35%">
					<p class="price"><?php echo $product->get_price_html(); ?></p>
				</td>

			</tr>
		<?php endforeach; ?>

	</tbody></table>

<?php endif; ?>