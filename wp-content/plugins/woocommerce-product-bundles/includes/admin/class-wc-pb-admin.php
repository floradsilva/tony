<?php
/**
 * WC_PB_Admin class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Bundles Admin Class.
 *
 * Loads admin scripts, includes admin classes and adds admin hooks.
 *
 * @class    WC_PB_Admin
 * @version  5.9.0
 */
class WC_PB_Admin {

	/**
	 * Setup Admin class.
	 */
	public static function init() {

		add_action( 'init', array( __CLASS__, 'admin_init' ) );

		// Add a message in the WP Privacy Policy Guide page.
		add_action( 'admin_init', array( __CLASS__, 'add_privacy_policy_guide_content' ) );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_scripts' ), 11 );

		// Add template override scan path in tracking info.
		add_filter( 'woocommerce_template_overrides_scan_paths', array( __CLASS__, 'template_scan_path' ) );

		// Add PB debug data in the system status.
		add_action( 'woocommerce_system_status_report', array( __CLASS__ , 'render_system_status_items' ) );

		// Add "Insufficient Stock" report tab.
		add_filter( 'woocommerce_admin_reports', array( __CLASS__, 'add_insufficient_stock_report_tab' ) );
		add_action( 'admin_print_styles', array( __CLASS__, 'maybe_add_insufficient_stock_report_notice' ) );

	}

	/**
	 * Message to add in the WP Privacy Policy Guide page.
	 *
	 * @since  5.7.10
	 *
	 * @return string
	 */
	protected static function get_privacy_policy_guide_message() {

		$content = '
			<div contenteditable="false">' .
				'<p class="wp-policy-help">' .
					__( 'Product Bundles does not collect, store or share any personal data.', 'woocommerce-product-bundles' ) .
				'</p>' .
			'</div>';

		return $content;
	}

	/**
	 * Admin init.
	 */
	public static function admin_init() {
		self::includes();
	}

	/**
	 * Inclusions.
	 */
	public static function includes() {

		// Product Import/Export.
		if ( WC_PB_Core_Compatibility::is_wc_version_gte( '3.1' ) ) {
			require_once( 'export/class-wc-pb-product-export.php' );
			require_once( 'import/class-wc-pb-product-import.php' );
		}

		// Product Metaboxes.
		require_once( 'meta-boxes/class-wc-pb-meta-box-product-data.php' );

		// Post type stuff.
		require_once( 'class-wc-pb-admin-post-types.php' );

		// Admin AJAX.
		require_once( 'class-wc-pb-admin-ajax.php' );

		// Admin edit-order screen.
		if ( WC_PB_Core_Compatibility::is_wc_version_gte( '3.2' ) ) {
			require_once( 'class-wc-pb-admin-order.php' );
		}
	}

	/**
	 * Add a message in the WP Privacy Policy Guide page.
	 *
	 * @since  5.7.10
	 */
	public static function add_privacy_policy_guide_content() {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content( 'WooCommerce Product Bundles', self::get_privacy_policy_guide_message() );
		}
	}

	/**
	 * Admin writepanel scripts.
	 */
	public static function admin_scripts() {

		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_register_script( 'wc-pb-admin-product-panel', WC_PB()->plugin_url() . '/assets/js/admin/meta-boxes-product' . $suffix . '.js', array( 'wc-admin-product-meta-boxes' ), WC_PB()->version );
		wp_register_script( 'wc-pb-admin-order-panel', WC_PB()->plugin_url() . '/assets/js/admin/meta-boxes-order' . $suffix . '.js', array( 'wc-admin-order-meta-boxes' ), WC_PB()->version );

		wp_register_style( 'wc-pb-admin-css', WC_PB()->plugin_url() . '/assets/css/admin/admin.css', array(), WC_PB()->version );
		wp_style_add_data( 'wc-pb-admin-css', 'rtl', 'replace' );

		wp_register_style( 'wc-pb-admin-product-css', WC_PB()->plugin_url() . '/assets/css/admin/meta-boxes-product.css', array( 'woocommerce_admin_styles' ), WC_PB()->version );
		wp_style_add_data( 'wc-pb-admin-product-css', 'rtl', 'replace' );

		wp_register_style( 'wc-pb-admin-edit-order-css', WC_PB()->plugin_url() . '/assets/css/admin/meta-boxes-order.css', array( 'woocommerce_admin_styles' ), WC_PB()->version );
		wp_style_add_data( 'wc-pb-admin-edit-order-css', 'rtl', 'replace' );

		wp_enqueue_style( 'wc-pb-admin-css' );

		// Get admin screen ID.
		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		/*
		 * Enqueue styles.
		 */
		if ( in_array( $screen_id, array( 'edit-product', 'product' ) ) ) {
			wp_enqueue_style( 'wc-pb-admin-product-css' );
		} elseif ( in_array( $screen_id, array( 'shop_order', 'edit-shop_order', 'shop_subscription', 'edit-shop_subscription' ) ) ) {
			wp_enqueue_style( 'wc-pb-admin-edit-order-css' );
		}

		/*
		 * Enqueue scripts.
		 */
		if ( 'product' === $screen_id ) {

			wp_enqueue_script( 'wc-pb-admin-product-panel' );

			// Find group modes with a parent item.
			$group_mode_options      = WC_Product_Bundle::get_group_mode_options();
			$group_modes_with_parent = array();

			foreach ( $group_mode_options as $group_mode_key => $group_mode_title ) {
				if ( WC_Product_Bundle::group_mode_has( $group_mode_key, 'parent_item' ) || WC_Product_Bundle::group_mode_has( $group_mode_key, 'faked_parent_item' ) ) {
					$group_modes_with_parent[] = $group_mode_key;
				}
			}

			$params = array(
				'add_bundled_product_nonce' => wp_create_nonce( 'wc_bundles_add_bundled_product' ),
				'group_modes_with_parent'   => $group_modes_with_parent,
				'is_first_bundle'           => isset( $_GET[ 'wc_pb_first_bundle' ] ) ? 'yes' : 'no',
				'is_wc_version_gte_3_2'     => WC_PB_Core_Compatibility::is_wc_version_gte( '3.2' ) ? 'yes' : 'no'
			);

			wp_localize_script( 'wc-pb-admin-product-panel', 'wc_bundles_admin_params', $params );

		} elseif ( 'edit-product' === $screen_id ) {

			wc_enqueue_js( "
				jQuery( function( $ ) {
					jQuery( '.show_insufficient_stock_items' ).on( 'click', function() {
						var anchor = jQuery( this ),
							panel  = jQuery( this ).parent().find( '.insufficient_stock_items' );

						if ( anchor.hasClass( 'closed' ) ) {
							anchor.removeClass( 'closed' );
							panel.slideDown( 200 );
						} else {
							anchor.addClass( 'closed' );
							panel.slideUp( 200 );
						}
						return false;
					} );
				} );
			" );

		} elseif ( in_array( $screen_id, array( 'shop_order', 'shop_subscription' ) ) ) {

			wp_enqueue_script( 'wc-pb-admin-order-panel' );

			$params = array(
				'edit_bundle_nonce'     => wp_create_nonce( 'wc_bundles_edit_bundle' ),
				'is_wc_version_gte_3_4' => WC_PB_Core_Compatibility::is_wc_version_gte( '3.4' ) ? 'yes' : 'no',
				'is_wc_version_gte_3_6' => WC_PB_Core_Compatibility::is_wc_version_gte( '3.6' ) ? 'yes' : 'no',
				'i18n_configure'        => __( 'Configure', 'woocommerce-product-bundles' ),
				'i18n_edit'             => __( 'Edit', 'woocommerce-product-bundles' ),
				'i18n_form_error'       => __( 'Failed to initialize form. If this issue persists, please reload the page and try again.', 'woocommerce-product-bundles' ),
				'i18n_validation_error' => __( 'Failed to validate configuration. If this issue persists, please reload the page and try again.', 'woocommerce-product-bundles' )
			);

			wp_localize_script( 'wc-pb-admin-order-panel', 'wc_bundles_admin_order_params', $params );
		}
	}

	/**
	 * Support scanning for template overrides in extension.
	 *
	 * @param  array  $paths
	 * @return array
	 */
	public static function template_scan_path( $paths ) {

		$paths[ 'WooCommerce Product Bundles' ] = WC_PB()->plugin_path() . '/templates/';

		return $paths;
	}

	/**
	 * Add PB debug data in the system status.
	 *
	 * @since  5.7.9
	 */
	public static function render_system_status_items() {

		$debug_data = array(
			'db_version' => get_option( 'woocommerce_product_bundles_db_version', null ),
			'overrides'  => self::get_template_overrides()
		);

		include( 'views/html-admin-page-status-report.php' );
	}

	/**
	 * Determine which of our files have been overridden by the theme.
	 *
	 * @since  5.7.9
	 *
	 * @return array
	 */
	private static function get_template_overrides() {

		$template_path    = WC_PB()->plugin_path() . '/templates/';
		$templates        = WC_Admin_Status::scan_template_files( $template_path );
		$wc_template_path = trailingslashit( WC()->template_path() );
		$theme_root       = trailingslashit( get_theme_root() );

		$overridden = array();

		foreach ( $templates as $file ) {

			$found_location  = false;
			$check_locations = array(
				get_stylesheet_directory() . "/{$file}",
				get_stylesheet_directory() . "/{$wc_template_path}{$file}",
				get_template_directory() . "/{$file}",
				get_template_directory() . "/{$wc_template_path}{$file}"
			);

			foreach ( $check_locations as $location ) {
				if ( is_readable( $location ) ) {
					$found_location = $location;
					break;
				}
			}

			if ( ! empty( $found_location ) ) {

				$core_version  = WC_Admin_Status::get_file_version( $template_path . $file );
				$found_version = WC_Admin_Status::get_file_version( $found_location );
				$is_outdated   = $core_version && ( empty( $found_version ) || version_compare( $found_version, $core_version, '<' ) );

				if ( false !== strpos( $found_location, '.php' ) ) {
					$overridden[] = array(
						'file'         => str_replace( $theme_root, '', $found_location ),
						'version'      => $found_version,
						'core_version' => $core_version,
						'is_outdated'  => $is_outdated,
					);
				}
			}
		}

		return $overridden;
	}

	/**
	 * Adds an "Insufficient stock" tab to the WC stock reports.
	 *
	 * @param  array  $reports
	 * @return array
	 */
	public static function add_insufficient_stock_report_tab( $reports ) {

		$reports[ 'stock' ][ 'reports' ][ 'insufficient_stock' ] = array(
			'title'       => __( 'Insufficient stock', 'woocommerce-product-bundles' ),
			'description' => '',
			'hide_title'  => true,
			'callback'    => array( __CLASS__, 'get_insufficient_stock_report_content' )
		);

		return $reports;
	}

	/**
	 * Renders the "Insufficient stock" report content.
	 *
	 * @param  string  $name
	 * @return void
	 */
	public static function get_insufficient_stock_report_content( $name ) {

		require_once( 'reports/class-wc-pb-report-insufficient-stock.php' );

		$report = new WC_PB_Report_Insufficient_Stock;
		$report->output_report();
	}

	/**
	 * Renders a notice in the "Insufficient stock" report page.
	 *
	 * @since  5.9.0
	 *
	 * @return void
	 */
	public static function maybe_add_insufficient_stock_report_notice() {

		$screen    = get_current_screen();
		$screen_id = $screen ? $screen->id : '';

		if ( 'woocommerce_page_wc-reports' !== $screen_id ) {
			return;
		}

		if ( empty( $_GET[ 'bundle_id' ] ) ) {
			return;
		}

		$bundle = wc_get_product( absint( $_GET[ 'bundle_id' ] ) );
		$notice = sprintf( __( 'You are currently viewing a filtered version of this report for <strong>%1$s</strong>. <a href="%2$s" class="wc_pb_forward">Clear Filter</a>', 'woocommerce-product-bundles' ), $bundle->get_title(), admin_url( 'admin.php?page=wc-reports&tab=stock&report=insufficient_stock' ) );
		WC_PB_Admin_Notices::add_notice( $notice, 'info' );
	}
}

WC_PB_Admin::init();
