<?php
/**
 * WCS_ATT_Admin class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce All Products For Subscriptions
 * @since    1.0.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin includes and hooks.
 *
 * @class    WCS_ATT_Admin
 * @version  2.3.1
 */
class WCS_ATT_Admin {

	/**
	 * Initialize.
	 */
	public static function init() {
		self::add_hooks();
	}

	/**
	 * Add hooks.
	 */
	private static function add_hooks() {

		/*
		 * Single-Product settings.
		 */

		// Metabox includes.
		add_action( 'init', array( __CLASS__, 'admin_init' ) );

		// Admin scripts and styles.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ) );

		/*
		 * Subscribe-to-Cart settings.
		 */

		// Append "Subscribe to Cart/Order" section in the Subscriptions settings tab.
		add_filter( 'woocommerce_subscription_settings', array( __CLASS__, 'add_settings' ), 100 );

		// Save posted cart subscription scheme settings.
		add_action( 'woocommerce_update_options_subscriptions', array( __CLASS__, 'save_cart_level_settings' ) );

		// Display subscription scheme admin metaboxes in the "Subscribe to Cart/Order" section.
		add_action( 'woocommerce_admin_field_subscription_schemes', array( __CLASS__, 'subscription_schemes_content' ) );

		/*
		 * Extra 'Allow Switching' checkboxes.
		 */

		add_filter( 'woocommerce_subscriptions_allow_switching_options', array( __CLASS__, 'allow_switching_options' ) );
	}

	/**
	 * Admin init.
	 */
	public static function admin_init() {
		self::includes();
	}

	/**
	 * Include classes.
	 */
	public static function includes() {

		if ( WCS_ATT_Core_Compatibility::is_wc_version_gte( '3.1' ) ) {
			require_once( 'export/class-wcs-att-product-export.php' );
			require_once( 'import/class-wcs-att-product-import.php' );
		}

		require_once( 'class-wcs-att-admin-ajax.php' );
		require_once( 'meta-boxes/class-wcs-att-meta-box-product-data.php' );
	}

	/**
	 * Add extra 'Allow Switching' options.
	 *
	 * @param  array  $data
	 * @return array
	 */
	public static function allow_switching_options( $data ) {
		return array_merge( $data, array(
			array(
				'id'    => 'product_plans',
				'label' => __( 'Between Subscription Plans', 'woocommerce-all-products-for-subscriptions' )
			)
		) );
	}

	/**
	 * Subscriptions schemes admin metaboxes.
	 *
	 * @param  array  $values
	 * @return void
	 */
	public static function subscription_schemes_content( $values ) {

		$subscription_schemes = get_option( 'wcsatt_subscribe_to_cart_schemes', array() );

		?><tr valign="top">
			<th scope="row" class="titledesc"><?php echo esc_html( $values[ 'title' ] ) ?></th>
			<td class="forminp forminp-subscription_schemes_metaboxes">
				<p class="description"><?php echo esc_html( $values[ 'desc' ] ) ?></p>
				<div id="wcsatt_data" class="wc-metaboxes-wrapper <?php echo empty( $subscription_schemes ) ? 'planless' : ''; ?>">
					<div class="subscription_schemes wc-metaboxes ui-sortable" data-count=""><?php

						$i = 0;

						foreach ( $subscription_schemes as $subscription_scheme ) {
							do_action( 'wcsatt_subscription_scheme', $i, $subscription_scheme, '' );
							$i++;
						}

					?></div>
					<p class="subscription_schemes_add_wrapper">
						<button type="button" class="button add_subscription_scheme"><?php _e( 'Add Plan', 'woocommerce-all-products-for-subscriptions' ); ?></button>
					</p>
				</div>
			</td>
		</tr><?php
	}

	/**
	 * Append "Subscribe to Cart/Order" section in the Subscriptions settings tab.
	 *
	 * @since  2.1.0
	 *
	 * @param  array  $settings
	 * @return array
	 */
	public static function add_settings( $settings ) {

		// Insert before miscellaneous settings.
		$misc_section_start = wp_list_filter( $settings, array( 'id' => 'woocommerce_subscriptions_miscellaneous', 'type' => 'title' ) );

		$spliced_array = array_splice( $settings, key( $misc_section_start ), 0, array(
			array(
				'name' => __( 'Subscribe to Cart', 'woocommerce-all-products-for-subscriptions' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wcsatt_subscribe_to_cart_options'
			),
			array(
				'name' => __( 'Cart Subscription Plans', 'woocommerce-all-products-for-subscriptions' ),
				'desc' => __( 'Subscription plans offered on the cart page.', 'woocommerce-all-products-for-subscriptions' ),
				'id'   => 'wcsatt_subscribe_to_cart_schemes',
				'type' => 'subscription_schemes'
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wcsatt_subscribe_to_cart_options'
			),
			array(
				'name' => __( 'Add to Subscription', 'woocommerce-all-products-for-subscriptions' ),
				'type' => 'title',
				'desc' => '',
				'id'   => 'wcsatt_add_to_subscription_options'
			),
			array(
				'name'     => __( 'Products', 'woocommerce-all-products-for-subscriptions' ),
				'desc'     => __( 'Allow customers to add products to existing subscriptions.', 'woocommerce-all-products-for-subscriptions' ),
				'id'       => 'wcsatt_add_product_to_subscription',
				'type'     => 'select',
				'options'  => array(
					'off'              => _x( 'Off', 'adding a product to an existing subscription', 'woocommerce-all-products-for-subscriptions' ),
					'matching_schemes' => _x( 'On For Products With Subscription Plans', 'adding a product to an existing subscription', 'woocommerce-all-products-for-subscriptions' ),
					'on'               => _x( 'On', 'adding a product to an existing subscription', 'woocommerce-all-products-for-subscriptions' ),
				),
				'desc_tip' => true
			),
			array(
				'name'     => __( 'Carts', 'woocommerce-all-products-for-subscriptions' ),
				'desc'     => __( 'Allow customers to add their cart to an existing subscription.', 'woocommerce-all-products-for-subscriptions' ),
				'id'       => 'wcsatt_add_cart_to_subscription',
				'type'     => 'select',
				'options'  => array(
					'off'      => _x( 'Off', 'adding a cart to an existing subscription', 'woocommerce-all-products-for-subscriptions' ),
					'on'       => _x( 'On', 'adding a cart to an existing subscription', 'woocommerce-all-products-for-subscriptions' ),
				),
				'desc_tip' => true
			),
			array(
				'type' => 'sectionend',
				'id'   => 'wcsatt_add_to_subscription_options'
			)
		) );

		return $settings;
	}

	/**
	 * Save subscription scheme option from the WooCommerce > Settings > Subscriptions administration screen.
	 *
	 * @return void
	 */
	public static function save_cart_level_settings() {

		if ( isset( $_POST[ 'wcsatt_schemes' ] ) ) {
			$posted_schemes = $_POST[ 'wcsatt_schemes' ];
		} else {
			$posted_schemes = array();
		}

		$posted_schemes = stripslashes_deep( $posted_schemes );
		$unique_schemes = array();

		foreach ( $posted_schemes as $posted_scheme ) {

			// Construct scheme id.
			$scheme_id = $posted_scheme[ 'subscription_period_interval' ] . '_' . $posted_scheme[ 'subscription_period' ] . '_' . $posted_scheme[ 'subscription_length' ];

			$unique_schemes[ $scheme_id ]         = $posted_scheme;
			$unique_schemes[ $scheme_id ][ 'id' ] = $scheme_id;
		}

		update_option( 'wcsatt_subscribe_to_cart_schemes', $unique_schemes );
	}

	/**
	 * Load scripts and styles.
	 *
	 * @return void
	 */
	public static function admin_scripts() {

		global $post;

		// Get admin screen id.
		$screen      = get_current_screen();
		$screen_id   = $screen ? $screen->id : '';

		$add_scripts = false;
		$suffix      = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {
			$add_scripts             = true;
			$writepanel_dependencies = array( 'jquery', 'jquery-ui-datepicker', 'wc-admin-meta-boxes', 'wc-admin-product-meta-boxes' );
		} elseif ( $screen_id === 'woocommerce_page_wc-settings' && isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] === 'subscriptions' ) {
			$add_scripts             = true;
			$writepanel_dependencies = array( 'jquery', 'jquery-ui-datepicker' );
		}

		if ( $add_scripts ) {
			wp_register_script( 'wcsatt-writepanel', WCS_ATT()->plugin_url() . '/assets/js/admin/meta-boxes' . $suffix . '.js', $writepanel_dependencies, WCS_ATT::VERSION );
			wp_register_style( 'wcsatt-writepanel-css', WCS_ATT()->plugin_url() . '/assets/css/admin/meta-boxes.css', array( 'woocommerce_admin_styles' ), WCS_ATT::VERSION );
			wp_style_add_data( 'wcsatt-writepanel-css', 'rtl', 'replace' );
			wp_enqueue_style( 'wcsatt-writepanel-css' );
		}

		// Enqueued in 'WCS_ATT_Admin_Notices'.
		wp_register_style( 'wcsatt-admin-css', WCS_ATT()->plugin_url() . '/assets/css/admin/admin.css', array(), WCS_ATT::VERSION );
		wp_enqueue_style( 'wcsatt-admin-css' );

		// WooCommerce admin pages.
		if ( in_array( $screen_id, array( 'product', 'woocommerce_page_wc-settings' ) ) ) {

			wp_enqueue_script( 'wcsatt-writepanel' );

			$params = array(
				'add_subscription_scheme_nonce'      => wp_create_nonce( 'wcsatt_add_subscription_scheme' ),
				'subscription_lengths'               => wcs_get_subscription_ranges(),
				'i18n_do_no_sync'                    => __( 'Disabled', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_inherit_option'                => __( 'Inherit from product', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_inherit_option_variable'       => __( 'Inherit from chosen variation', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_override_option'               => __( 'Override product', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_override_option_variable'      => __( 'Override all variations', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_discount_description'          => __( 'Discount applied over the <strong>Regular Price</strong> of the product.', 'woocommerce-all-products-for-subscriptions' ),
				'i18n_discount_description_variable' => __( 'Discount applied over the <strong>Regular Price</strong> of the chosen variation.', 'woocommerce-all-products-for-subscriptions' ),
				'is_onboarding'                      => isset( $_GET[ 'wcsatt_onboarding' ] ) ? 'yes' : 'no',
				'wc_ajax_url'                        => admin_url( 'admin-ajax.php' ),
				'post_id'                            => is_object( $post ) ? $post->ID : '',
			);

			wp_localize_script( 'wcsatt-writepanel', 'wcsatt_admin_params', $params );
		}
	}
}

WCS_ATT_Admin::init();
