<?php
/**
 * Plugin Name: Wdm Subscription Split Payment
 * Description: A plugin to split payments for products using WooCommerce Subscription.
 * Version: 1.0.0
 * Author: Wisdmlabs
 * Author URI: https://wisdmlabs.com
 * Text Domain: wdm-subscription-split-payment
 * License: GPL2+
 */


if ( ! in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	echo __( 'Please activate WooCommerce Subscription Plugin before activating the plugin.', 'wdm-ebridge-woocommerce-sync' );
	die;
}


/* Define WC_PLUGIN_FILE. */
if ( ! defined( 'WSSP_PLUGIN_PATH' ) ) {
	define( 'WSSP_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

// Load dependencies.
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wssp-shipping-fields.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-wssp-shipping-orders.php';


// Initialize our classes.
WSSP_Shipping_Fields::init();
WSSP_Shipping_Orders::init();
