<?php
	$features = array(
		__( 'Automatic updates of exchange rates.', 'wc-price-based-country' ),
		__( 'Round up to nearest.', 'wc-price-based-country' ),
		__( 'Extra fee to exchange rate.', 'wc-price-based-country' ),
		__( 'Display the currency code next to price.', 'wc-price-based-country' ),
		__( 'Thousand separator, decimal separator and number of decimals by pricing zone.', 'wc-price-based-country' ),
		__( 'Currency switcher widget.', 'wc-price-based-country' ),
		__( 'Support for manual orders.', 'wc-price-based-country' ),
		__( 'Support for the import/export WooCommerce tool.', 'wc-price-based-country' )
	);
?>
<a href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro" class="wc-price-based-country-sidebar-section main" target="_blank" rel="noopener noreferrer">
	<h2><span class="feature_text"><?php _e( 'Upgrade to Pro version', 'wc-price-based-country' ); ?></h2>
</a>
<a href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro" class="wc-price-based-country-sidebar-section" target="_blank" rel="noopener noreferrer">
	<div class="section-title">
		<div class="dashicons dashicons-star-filled"></div>
		<h3><?php _e( 'Professional features', 'wc-price-based-country' ); ?></h3>
	</div>
	<ul class="feature-list"><?php foreach ( $features as $feature ) : ?>
		<li><?php echo $feature; ?></li><?php endforeach; ?>
	</ul>
</a>
<a href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro" class="wc-price-based-country-sidebar-section" target="_blank" rel="noopener noreferrer">
	<div class="section-title">
		<div class="dashicons dashicons-woocommerce"></div>
		<h3><?php _e( 'Compatible with most popular WooCommerce plugins', 'wc-price-based-country' ); ?></h3>
	</div>
	<ul class="feature-list">
		<li>WooCommerce Product Add-ons</li>
		<?php foreach ( array_unique( wcpbc_product_types_supported( 'pro' ) ) as $feature ) : ?>
		<li><?php echo $feature; ?></li><?php endforeach; ?>
	</ul>
</a>
<div class="wc-price-based-country-sidebar-section">
	<p><span class="dashicons dashicons-thumbs-up"> </span><?php _e( 'No ads', 'wc-price-based-country' ); ?></p>
	<p><span class="dashicons dashicons-thumbs-up"> </span><?php _e( 'Guaranteed support', 'wc-price-based-country' ); ?></p>
	<p><span class="dashicons dashicons-thumbs-up"> </span><?php _e( 'More features and integrations is coming', 'wc-price-based-country' ); ?></p>
	<p class="cta">
		<a target="_blank" rel="noopener noreferrer" class="cta-button" href="https://www.pricebasedcountry.com/pricing/?utm_source=settings&utm_medium=banner&utm_campaign=Get_Pro">
			<?php _e( 'Upgrade to Pro version now!', 'wc-price-based-country' ); ?>
		</a>
	</p>
</div>
