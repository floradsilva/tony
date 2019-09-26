<?php
/**
 * WC_PB_Admin_Ajax class
 *
 * @author   SomewhereWarm <info@somewherewarm.gr>
 * @package  WooCommerce Product Bundles
 * @since    5.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin AJAX meta-box handlers.
 *
 * @class    WC_PB_Admin_Ajax
 * @version  5.12.1
 */
class WC_PB_Admin_Ajax {

	/**
	 * Hook in.
	 */
	public static function init() {

		/*
		 * Notices.
		 */

		// Dismiss notices.
		add_action( 'wp_ajax_woocommerce_dismiss_bundle_notice', array( __CLASS__ , 'dismiss_notice' ) );

		/*
		 * Edit-Product screens.
		 */

		// Ajax add bundled product.
		add_action( 'wp_ajax_woocommerce_add_bundled_product', array( __CLASS__, 'ajax_add_bundled_product' ) );

		// Ajax search bundled item variations.
		add_action( 'wp_ajax_woocommerce_search_bundled_variations', array( __CLASS__, 'ajax_search_bundled_variations' ) );

		/*
		 * Edit-Order screens.
		 */

		// Ajax handler used to fetch form content for populating "Configure/Edit" bundle order item modals.
		add_action( 'wp_ajax_woocommerce_configure_bundle_order_item', array( __CLASS__, 'ajax_bundle_order_item_form' ) );

		// Ajax handler for editing bundles in manual/editable orders.
		add_action( 'wp_ajax_woocommerce_edit_bundle_in_order', array( __CLASS__, 'ajax_edit_bundle_in_order' ) );
	}

	/*
	|--------------------------------------------------------------------------
	| Notices.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Dismisses notices.
	 *
	 * @since  5.8.0
	 *
	 * @return void
	 */
	public static function dismiss_notice() {

		$failure = array(
			'result' => 'failure'
		);

		if ( ! check_ajax_referer( 'wc_pb_dismiss_notice_nonce', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST[ 'notice' ] ) ) {
			wp_send_json( $failure );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json( $failure );
		}

		$dismissed = WC_PB_Admin_Notices::dismiss_notice( wc_clean( $_POST[ 'notice' ] ) );

		if ( ! $dismissed ) {
			wp_send_json( $failure );
		}

		$response = array(
			'result' => 'success'
		);

		wp_send_json( $response );
	}

	/*
	|--------------------------------------------------------------------------
	| Edit-Product.
	|--------------------------------------------------------------------------
	*/

	/**
	 * Ajax search for bundled variations.
	 */
	public static function ajax_search_bundled_variations() {

		if ( ! empty( $_GET[ 'include' ] ) ) {
			if ( $product = wc_get_product( absint( $_GET[ 'include' ] ) ) ) {
				$_GET[ 'include' ] = $product->get_children();
			} else {
				$_GET[ 'include' ] = array();
			}
		}

		add_filter( 'woocommerce_json_search_found_products', array( __CLASS__, 'tweak_variation_titles' ) );
		WC_AJAX::json_search_products( '', true );
		remove_filter( 'woocommerce_json_search_found_products', array( __CLASS__, 'tweak_variation_titles' ) );
	}

	/**
	 * Tweak variation titles for consistency across different WC versions.
	 *
	 * @param  array  $search_results
	 * @return array
	 */
	public static function tweak_variation_titles( $search_results ) {

		if ( ! empty( $search_results ) ) {

			$search_result_objects = array_map( 'wc_get_product', array_keys( $search_results ) );

			foreach ( $search_result_objects as $variation ) {
				if ( $variation && $variation->is_type( 'variation' ) ) {
					$variation_id                    = $variation->get_id();
					$search_results[ $variation_id ] = rawurldecode( WC_PB_Helpers::get_product_variation_title( $variation, 'flat' ) );
				}
			}
		}

		return $search_results;
	}

	/**
	 * Handles adding bundled products via ajax.
	 */
	public static function ajax_add_bundled_product() {

		check_ajax_referer( 'wc_bundles_add_bundled_product', 'security' );

		$loop               = intval( $_POST[ 'id' ] );
		$post_id            = intval( $_POST[ 'post_id' ] );
		$product_id         = intval( $_POST[ 'product_id' ] );
		$item_id            = false;
		$toggle             = 'open';
		$tabs               = WC_PB_Meta_Box_Product_Data::get_bundled_product_tabs();
		$product            = wc_get_product( $product_id );
		$title              = $product->get_title();
		$sku                = $product->get_sku();
		$stock_status       = 'in_stock';
		$item_data          = array();
		$response           = array(
			'markup'  => '',
			'message' => ''
		);

		if ( $product ) {

			if ( in_array( $product->get_type(), array( 'simple', 'variable', 'subscription', 'variable-subscription' ) ) ) {

				if ( ! $product->is_in_stock() ) {
					$stock_status       = 'out_of_stock';
				} elseif ( $product->is_on_backorder( 1 ) ) {
					$stock_status       = 'on_backorder';
				}

				ob_start();
				include( 'meta-boxes/views/html-bundled-product.php' );
				$response[ 'markup' ] = ob_get_clean();

			} else {
				$response[ 'message' ] = __( 'The selected product cannot be bundled. Please select a simple product, a variable product, or a simple/variable subscription.', 'woocommerce-product-bundles' );
			}

		} else {
			$response[ 'message' ] = __( 'The selected product is invalid.', 'woocommerce-product-bundles' );
		}

		wp_send_json( $response );
	}

	/*
	|--------------------------------------------------------------------------
	| Edit-Order.
	|--------------------------------------------------------------------------
	*/

	/**
	 * True when displaying content in an edit-composite order item modal.
	 *
	 * @since  3.14.0
	 *
	 * @return void
	 */
	public static function is_bundle_edit_request() {
		return doing_action( 'wp_ajax_woocommerce_edit_bundle_in_order' );
	}

	/**
	 * Form content used to populate "Configure/Edit" bundle order item modals.
	 *
	 * @since  5.8.0
	 *
	 * @return void
	 */
	public static function ajax_bundle_order_item_form() {

		global $product;

		$failure = array(
			'result' => 'failure'
		);

		if ( ! check_ajax_referer( 'wc_bundles_edit_bundle', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST[ 'order_id' ] ) || empty( $_POST[ 'item_id' ] ) ) {
			wp_send_json( $failure );
		}

		$order   = wc_get_order( wc_clean( $_POST[ 'order_id' ] ) );
		$item_id = absint( wc_clean( $_POST[ 'item_id' ] ) );

		if ( ! ( $order instanceof WC_Order ) ) {
			wp_send_json( $failure );
		}

		$item = $order->get_item( $item_id );

		if ( ! ( $item instanceof WC_Order_Item ) ) {
			wp_send_json( $failure );
		}

		$product       = $item->get_product();
		$bundled_items = $product ? $product->get_bundled_items() : false;

		if ( empty( $bundled_items ) ) {
			wp_send_json( $failure );
		}

		// Initialize form state based on the actual configuration of the bundle.
		$configuration = WC_PB_Order::get_current_bundle_configuration( $item, $order );

		if ( ! empty( $configuration ) ) {
			$_REQUEST = array_merge( $_REQUEST, WC_PB()->cart->rebuild_posted_bundle_form_data( $configuration ) );
		}

		// Force tabular layout.
		$product->set_layout( 'tabular' );

		// Hide prices.
		add_filter( 'woocommerce_bundled_item_is_priced_individually', '__return_false' );

		ob_start();
		include( 'meta-boxes/views/html-bundle-edit-form.php' );
		$html = ob_get_clean();

		$response = array(
			'result' => 'success',
			'html'   => $html
		);

		wp_send_json( $response );
	}

	/**
	 * Validates edited/configured bundles and returns updated order items.
	 *
	 * @since  5.8.0
	 *
	 * @return void
	 */
	public static function ajax_edit_bundle_in_order() {

		$failure = array(
			'result' => 'failure'
		);

		if ( ! current_user_can( 'edit_shop_orders' ) ) {
			wp_send_json( $failure );
		}

		if ( ! check_ajax_referer( 'wc_bundles_edit_bundle', 'security', false ) ) {
			wp_send_json( $failure );
		}

		if ( empty( $_POST[ 'order_id' ] ) || empty( $_POST[ 'item_id' ] ) ) {
			wp_send_json( $failure );
		}

		$order   = wc_get_order( wc_clean( $_POST[ 'order_id' ] ) );
		$item_id = absint( wc_clean( $_POST[ 'item_id' ] ) );

		if ( ! ( $order instanceof WC_Order ) ) {
			wp_send_json( $failure );
		}

		$item = $order->get_item( $item_id );

		if ( ! ( $item instanceof WC_Order_Item ) ) {
			wp_send_json( $failure );
		}

		$product = $item->get_product();

		if ( ! ( $product instanceof WC_Product_Bundle ) ) {
			wp_send_json( $failure );
		}

		if ( ! empty( $_POST[ 'fields' ] ) ) {
			parse_str( $_POST[ 'fields' ], $posted_form_fields );
			$_POST = array_merge( $_POST, $posted_form_fields );
		}

		$posted_configuration  = WC_PB()->cart->get_posted_bundle_configuration( $product );
		$current_configuration = WC_PB_Order::get_current_bundle_configuration( $item, $order );

		// Compare posted against current configuration.
		if ( $posted_configuration !== $current_configuration ) {

			$added_to_order = WC_PB()->order->add_bundle_to_order( $product, $order, $item->get_quantity(), array(
				'configuration' => $posted_configuration
			) );

			// Invalid configuration?
			if ( is_wp_error( $added_to_order ) ) {

				$message = __( 'The submitted configuration is invalid.', 'woocommerce-product-bundles' );
				$data    = $added_to_order->get_error_data();

				$notice = isset( $data[ 'notices' ] ) ? html_entity_decode( current( $data[ 'notices' ] ) ) : '';

				if ( $notice ) {
					$message = sprintf( _x( '%1$s %2$s', 'edit bundle in order: formatted validation message', 'woocommerce-product-bundles' ), $message, $notice );
				}

				$response = array(
					'result' => 'failure',
					'error'  => $message
				);

				wp_send_json( $response );

			// Remove old items.
			} else {

				if ( has_action( 'woocommerce_editing_bundle_in_order' ) ) {

					$new_container_item = $order->get_item( $added_to_order );

					/**
					 * 'woocommerce_editing_bundle_in_order' action.
					 *
					 * @since  5.9.2
					 *
					 * @param  WC_Order_Item_Product  $new_item
					 * @param  WC_Order_Item_Product  $old_item
					 */
					do_action( 'woocommerce_editing_bundle_in_order', $new_container_item, $item, $order );
				}

				$items_to_remove = array( $item ) + wc_pb_get_bundled_order_items( $item, $order );
				$pre_stock_map   = array();

				foreach ( $items_to_remove as $remove_item ) {

					if ( WC_PB_Core_Compatibility::is_wc_version_gte( '3.6' ) ) {

						$changed_stock = wc_maybe_adjust_line_item_product_stock( $remove_item, 0 );
						if ( $changed_stock && isset( $changed_stock[ 'from' ] ) ) {
							$bundled_item_id                   = $remove_item->get_meta( '_bundled_item_id' );
							$pre_stock_map[ $bundled_item_id ] = $changed_stock[ 'from' ];
						}
					}

					$order->remove_item( $remove_item->get_id() );
					$remove_item->delete();
				}

				if ( WC_PB_Core_Compatibility::is_wc_version_gte( '3.6' ) ) {

					$bundled_order_items = wc_pb_get_bundled_order_items( $order->get_item( $added_to_order ), $order );
					foreach ( $bundled_order_items as $order_item_id => $order_item ) {
						$product = $order_item->get_product();
						$qty     = $order_item->get_quantity();

						if ( $product->managing_stock() ) {
							$bundled_item_id = $order_item->get_meta( '_bundled_item_id' );
							$old_stock       = isset( $pre_stock_map[ $bundled_item_id ] ) ? $pre_stock_map[ $bundled_item_id ] : $product->get_stock_quantity();
							$new_stock       = wc_update_product_stock( $product, $qty, 'decrease' );

							if ( $old_stock && $old_stock !== $new_stock ) {
								$order->add_order_note( sprintf( __( 'Adjusted %s stock', 'woocommerce-product-bundles' ), $product->get_formatted_name() ) . ' (' . $old_stock . '&rarr;' . $new_stock . ')', false, true );
							}

							$order_item->add_meta_data( '_reduced_stock', $qty, true );
							$order_item->save();
						}
					}
				}

				unset( $pre_stock_map );

				if ( isset( $_POST[ 'country' ], $_POST[ 'state' ], $_POST[ 'postcode' ], $_POST[ 'city' ] ) ) {

					$calculate_tax_args = array(
						'country'  => strtoupper( wc_clean( $_POST[ 'country' ] ) ),
						'state'    => strtoupper( wc_clean( $_POST[ 'state' ] ) ),
						'postcode' => strtoupper( wc_clean( $_POST[ 'postcode' ] ) ),
						'city'     => strtoupper( wc_clean( $_POST[ 'city' ] ) ),
					);

					$order->calculate_taxes( $calculate_tax_args );
					$order->calculate_totals( false );

				} else {
					$order->save();
				}
			}
		}

		ob_start();
		include ( WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-items.php' );
		$html = ob_get_clean();

		if ( WC_PB_Core_Compatibility::is_wc_version_gte( '3.6' ) ) {

			ob_start();
			$notes = wc_get_order_notes( array( 'order_id' => $order->get_id() ) );
			include ( WC_ABSPATH . 'includes/admin/meta-boxes/views/html-order-notes.php' );
			$notes_html = ob_get_clean();
			$response = array(
				'result'     => 'success',
				'html'       => $html,
				'notes_html' => $notes_html
			);

		} else {
			$response = array(
				'result'     => 'success',
				'html'       => $html,
			);
		}

		wp_send_json( $response );
	}
}

WC_PB_Admin_Ajax::init();
