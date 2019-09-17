<?php
/**
 * Handle Price Based on Country admin ads.
 *
 * @version 1.8
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WCPBC_Admin_Ads Class
 */
class WCPBC_Admin_Ads {

	/**
	 * Init hooks
	 */
	public static function init() {
		if ( wcpbc_is_pro() ) {
			return;
		}
		add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'order_item_add_action_buttons' ) );
		add_action( 'woocommerce_variable_product_bulk_edit_actions', array( __CLASS__, 'variable_product_bulk_edit_actions' ) );
		// Pro product types.
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'display_pro_product_type_supported' ), 11 );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'display_pro_integration_notices' ), 11 );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'display_pro_product_type_supported' ), 11 );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'display_pro_integration_notices' ), 11 );
		add_action( 'woocommerce_bookings_after_bookings_pricing', array( __CLASS__, 'display_pro_product_type_supported' ) );
		add_filter( 'wc_price_based_country_admin_notices', array( __CLASS__, 'admin_notices' ) );
	}

	/**
	 * Display load country pricing button.
	 *
	 * @since 1.7.9
	 * @param WC_Order $order Order instance.
	 */
	public static function order_item_add_action_buttons( $order ) {
		if ( ! wcpbc_is_pro() && version_compare( WC_VERSION, '3.0', '>=' ) && $order->is_editable() ) {
			echo '<button type="button" class="button wcpbc-upgrade-pro-popup">' . esc_html__( 'Load country pricing', 'wc-price-based-country-pro' ) . '</button>';
			add_action( 'admin_footer', array( __CLASS__, 'upgrade_pro_popup' ) );
		}
	}

	/**
	 * Add variable bulk actions options
	 */
	public static function variable_product_bulk_edit_actions() {

		$variable_actions = array(
			__( 'Set regular prices', 'woocommerce' ),
			__( 'Increase regular prices (fixed amount or percentage)', 'woocommerce' ),
			__( 'Decrease regular prices (fixed amount or percentage)', 'woocommerce' ),
			__( 'Set sale prices', 'woocommerce' ),
			__( 'Increase sale prices (fixed amount or percentage)', 'woocommerce' ),
			__( 'Decrease sale prices (fixed amount or percentage)', 'woocommerce' ),
		);

		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			// Translators: 1: pricing zone name; 2: Pricing zone currency.
			echo '<optgroup label="' . esc_attr( sprintf( __( '%1$s Pricing (%2$s)', 'wc-price-based-country' ), $zone->get_name(), get_woocommerce_currency_symbol( $zone->get_currency() ) ) ) . '">';

			foreach ( $variable_actions as $key => $label ) {
				echo '<option value="wcpbc_variable_bulk_edit_popup">' . $label . '</option>'; //phpcs:ignore WordPress.Security.EscapeOutput
			}
			echo '</optgroup>';
		}

		add_action( 'admin_footer', array( __CLASS__, 'upgrade_pro_popup' ) );
	}

	/**
	 * Pro Product type supported
	 */
	public static function display_pro_product_type_supported() {
		foreach ( wcpbc_product_types_supported( 'pro' ) as $type => $name ) {
			$utm_source = 'product-' . $type;
			include dirname( __FILE__ ) . '/views/html-notice-pro-product-type.php';
		}
	}

	/**
	 * Pro version integrations ads.
	 *
	 * @param boolean $is_variation Is called by a woocommerce_product_after_variable_attributes.
	 */
	public static function display_pro_integration_notices( $is_variation = '' ) {
		if ( class_exists( 'WooCommerce_Germanized' ) || class_exists( 'WC_Smart_Coupons' ) ) {

			if ( class_exists( 'WooCommerce_Germanized' ) ) {
				$types = wcpbc_product_types_supported( false, 'product-data' );

				unset( $types['variable-subscription'] );
				unset( $types['booking'] );
				unset( $types['nyp-wcpbc'] );

				$type       = array_keys( $types );
				$utm_source = 'germanized-integration';
				$name       = 'Unit Prices of Germanized';

				include dirname( __FILE__ ) . '/views/html-notice-pro-product-type.php';
			}

			if ( class_exists( 'WC_Smart_Coupons' ) && '' === $is_variation ) {
				$type       = array( 'simple', 'variable', 'subscription', 'variable-subscription', 'subscription_variation' );
				$utm_source = 'wc-smart-coupons-integration';
				$name       = 'the Store credit/gift certificate of Smart Coupons';

				include dirname( __FILE__ ) . '/views/html-notice-pro-product-type.php';
			}
		}
	}

	/**
	 * Output the upgrade to Pro popup
	 *
	 * @since 1.8.0
	 */
	public static function upgrade_pro_popup() {
		add_thickbox();
		?>
			<div id="wcpbc-upgrade-pro-popup-content" style="display:none;">
				<h3 style="margin: 1em 0; font-size: 1.3em;">
				<?php
				if ( 'product' === get_post_type() ) {
					esc_html_e( 'Do you want to bulk edit the prices of the variations?', 'wc-price-based-country' );
					$utm_source = 'variations-bulk-edit';
				} else {
					esc_html_e( 'Do you need to add orders manually?', 'wc-price-based-country' );
					$utm_source = 'edit-order';
				}
				$url = 'https://www.pricebasedcountry.com/pricing/?utm_source=' . $utm_source . '&utm_medium=banner&utm_campaign=Get_Pro';
				?>
				</h3>
				<p>
				<?php
					// Translators: HTML tags.
					echo wp_kses_post( sprintf( __( 'Great news: you can, with %1$sPrice Based on Country Pro!%2$s' ), '<a href="' . $url . '">', '</a>' ) );
				?>
				</p>
				<p><?php esc_html_e( 'Other benefits of Pro version:', 'wc-price-based-country' ); ?></p>
				<ul>
					<li><span class="feature_text"><?php esc_html_e( 'Automatic updates of exchange rates.', 'wc-price-based-country' ); ?></span></li>
					<li><span class="feature_text"><?php esc_html_e( 'Round up to nearest.', 'wc-price-based-country' ); ?></span></li>
					<li><span class="feature_text"><?php esc_html_e( 'Currency switcher widget.', 'wc-price-based-country' ); ?></span></li>
					<li><span class="feature_text"><?php esc_html_e( 'Support for the import/export WooCommerce tool.', 'wc-price-based-country' ); ?></span></li>
					<li><span class="feature_text"><?php esc_html_e( 'Support to WooCommerce Subscriptions.', 'wc-price-based-country' ); ?></span></li>
					<li><span class="feature_text"><?php esc_html_e( 'Support to WooCommerce Product Add-ons.', 'wc-price-based-country' ); ?></span></li>
					<li><span class="feature_text"><?php esc_html_e( 'No ads!', 'wc-price-based-country' ); ?></span></li>
				</ul>
				<p><a target="_blank" rel="noopener noreferrer" class="button button-primary" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Upgrade to Price Based on Country Pro now!', 'wc-price-based-country' ); ?></a></p>
			</div>
		<?php
	}

	/**
	 * Add admin notices
	 *
	 * @param array $notices Admin notices.
	 * @return array
	 */
	public static function admin_notices( $notices ) {
		$notices['pro_csv_tool']      = array(
			'hide'     => 'no',
			'callback' => array( __CLASS__, 'display_pro_csv_tool_notice' ),
			'screens'  => array( 'product_page_product_importer', 'product_page_product_exporter' ),
		);
		$notices['pro_german_market'] = array(
			'hide'     => 'no',
			'callback' => array( __CLASS__, 'display_pro_german_market_notice' ),
			'screens'  => array( 'product' ),
		);
		return $notices;
	}

	/**
	 * Upgrade to Pro to import/export tool.
	 */
	public static function display_pro_csv_tool_notice() {
		$page          = isset( $_GET['page'] ) ? wc_clean( wp_unslash( $_GET['page'] ) ) : ''; // WPCS: CSRF ok.
		$import_export = ( 'product_importer' === $page ? __( 'import', 'wc-price-based-country' ) : __( 'export', 'wc-price-based-country' ) );
		include_once dirname( __FILE__ ) . '/views/html-notice-pro-csv-tool.php';
	}

	/**
	 * Upgrade to Pro German Market integration.
	 */
	public static function display_pro_german_market_notice() {
		if ( ! class_exists( 'Woocommerce_German_Market' ) ) {
			return;
		}

		include_once dirname( __FILE__ ) . '/views/html-notice-pro-german-market.php';
	}
}

