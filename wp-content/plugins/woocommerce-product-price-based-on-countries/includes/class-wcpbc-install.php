<?php
/**
 * WooCommerce Price Based Country install
 *
 * @version 1.8.5
 * @package WCPBC
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WCPBC_Install Class
 */
class WCPBC_Install {

	/**
	 * Min version that required a database update.
	 *
	 * @var string
	 */
	private static $update_version = '1.8.2';

	/**
	 * Database update files.
	 *
	 * @var array
	 */
	private static $db_updates = array(
		'1.3.2' => 'updates/wcpbc-update-1.3.2.php',
		'1.4.0' => 'updates/wcpbc-update-1.4.0.php',
		'1.5.0' => 'updates/wcpbc-update-1.5.0.php',
		'1.6.0' => 'updates/wcpbc-update-1.6.0.php',
		'1.6.2' => 'updates/wcpbc-update-1.6.2.php',
		'1.7.4' => 'updates/wcpbc-update-1.7.4.php',
		'1.8.2' => 'updates/wcpbc-update-1.8.2.php',
	);

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_init', array( __CLASS__, 'update_actions' ), 5 );
		add_action( 'admin_init', array( __CLASS__, 'check_version' ) );
		add_action( 'in_plugin_update_message-woocommerce-product-price-based-on-countries/woocommerce-product-price-based-on-countries.php', array( __CLASS__, 'in_plugin_update_message' ) );
	}

	/**
	 * Get version installed.
	 */
	private static function get_install_version() {
		$install_version = get_option( 'wc_price_based_country_version', null );
		if ( is_null( $install_version ) && get_option( '_oga_wppbc_countries_groups' ) ) {
			$install_version = '1.3.1';
		}

		return $install_version;
	}

	/**
	 * Update WCPBC version.
	 */
	private static function update_wcpbc_version() {
		update_option( 'wc_price_based_country_version', WCPBC()->version );
	}

	/**
	 * Sync exchange rate prices.
	 */
	public static function sync_exchange_rate_prices() {
		$zones = get_option( 'wc_price_based_country_regions', array() );
		foreach ( WCPBC_Pricing_Zones::get_zones() as $zone ) {
			wcpbc_sync_exchange_rate_prices( $zone );
		}
	}

	/**
	 * Install function
	 */
	public static function install() {
		$current_version = self::get_install_version();

		if ( null !== $current_version && version_compare( $current_version, self::$update_version, '<' ) ) {
			WCPBC_Admin_Notices::add_notice( 'update_db' );
		} else {
			// Update version.
			self::update_wcpbc_version();

			// Sync exchange rate prices.
			self::sync_exchange_rate_prices();
		}
	}

	/**
	 * Check_version function.
	 */
	public static function check_version() {
		$install_version = self::get_install_version();

		if ( ! defined( 'IFRAME_REQUEST' ) && version_compare( $install_version, self::$update_version, '<' ) ) {
			WCPBC_Admin_Notices::add_notice( 'update_db' );
		} elseif ( version_compare( $install_version, wcpbc()->version, '<' ) ) {
			// Update version.
			self::update_wcpbc_version();
		}
	}

	/**
	 * Handle updates.
	 */
	public static function update_actions() {

		if ( ! empty( $_GET['do_update_wc_price_based_country'] ) ) { // WPCS: CSRF ok.

			$install_version = self::get_install_version();

			foreach ( self::$db_updates as $version => $updater ) {
				if ( version_compare( $install_version, $version, '<' ) ) {
					include_once $updater;
				}
			}

			self::update_wcpbc_version();

			WCPBC_Admin_Notices::remove_notice( 'update_db' );
			WCPBC_Admin_Notices::add_notice( 'updated' );
		}
	}

	/**
	 * Update the Price Based on Country database to the latest version.
	 *
	 * @since 1.8.8
	 */
	public static function update_database() {
		$updater = end( self::$db_updates );
		include_once $updater;
		self::update_wcpbc_version();
		return __( 'Price Based on Country database update complete. Thank you for updating to the latest version!', 'wc-price-based-country' );
	}

	/**
	 * Display plugin upgrade notice.
	 *
	 * @param array $args An array of plugin metadata.
	 */
	public static function in_plugin_update_message( $args ) {

		$transient_name = 'wcpbc_upgrade_notice_' . $args['Version'];
		$upgrade_notice = get_transient( $transient_name );

		if ( false === $upgrade_notice ) {
			$response = wp_safe_remote_get( 'https://plugins.svn.wordpress.org/woocommerce-product-price-based-on-countries/trunk/readme.txt' );

			if ( ! is_wp_error( $response ) && ! empty( $response['body'] ) ) {
				$upgrade_notice = self::parse_update_notice( $response['body'] );
				set_transient( $transient_name, $upgrade_notice, DAY_IN_SECONDS );
			}
		}

		echo wp_kses_post( $upgrade_notice );
	}

	/**
	 * Parse update notice from readme file.
	 *
	 * @param  string $content Readme content.
	 * @return string
	 */
	private static function parse_update_notice( $content ) {
		// Output Upgrade Notice.
		$matches        = null;
		$regexp         = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote( WCPBC()->version ) . '\s*=|$)~Uis';
		$upgrade_notice = '';

		if ( preg_match( $regexp, $content, $matches ) ) {
			$version = trim( $matches[1] );
			$notices = (array) preg_split( '~[\r\n]+~', trim( $matches[2] ) );

			if ( version_compare( WCPBC()->version, $version, '<' ) ) {

				$upgrade_notice .= '<span class="wc_plugin_upgrade_notice wc_pbc_upgrade_notice">';

				foreach ( $notices as $index => $line ) {
					$upgrade_notice .= wp_kses_post( preg_replace( '~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line ) );
				}

				$upgrade_notice .= '</span> ';
			}
		}

		return $upgrade_notice;
	}
}
