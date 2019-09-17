<?php
/**
 * Plugin Name: AutomateWoo
 * Plugin URI: https://automatewoo.com
 * Description: Powerful marketing automation for your WooCommerce store.
 * Version: 4.6.0
 * Author: WooCommerce
 * Author URI: https://woocommerce.com
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl-3.0
 * Text Domain: automatewoo
 * Domain Path: /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 3.7
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AutomateWoo
 */

defined( 'ABSPATH' ) || exit;

define( 'AUTOMATEWOO_NAME', __( 'AutomateWoo', 'automatewoo' ) );
define( 'AUTOMATEWOO_SLUG', 'automatewoo' );
define( 'AUTOMATEWOO_VERSION', '4.6.0' );
define( 'AUTOMATEWOO_FILE', __FILE__ );
define( 'AUTOMATEWOO_PATH', dirname( __FILE__ ) );
define( 'AUTOMATEWOO_MIN_PHP_VER', '5.4' );
define( 'AUTOMATEWOO_MIN_WP_VER', '4.4.0' );
define( 'AUTOMATEWOO_MIN_WC_VER', '3.0.0' );


/**
 * AutomateWoo loader.
 *
 * @since 2.9
 */
class AutomateWoo_Loader {

	/**
	 * Contains load errors.
	 *
	 * @var array
	 */
	public static $errors = array();

	/**
	 * Init loader.
	 */
	public static function init() {
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ), 8 );
		// Ensure core before AutomateWoo add-ons.
		add_action( 'plugins_loaded', array( __CLASS__, 'load' ), 8 );
		add_action( 'plugins_loaded', array( __CLASS__, 'load_textdomain' ) );
	}

	/**
	 * Loads plugin.
	 */
	public static function load() {
		if ( self::check() ) {
			require_once AUTOMATEWOO_PATH . '/includes/automatewoo.php';
		}
	}

	/**
	 * Loads plugin textdomain.
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'automatewoo', false, 'automatewoo/languages' );
	}

	/**
	 * Checks if the plugin should load.
	 *
	 * @return bool
	 */
	public static function check() {
		$passed = true;

		$inactive_text = '<strong>' . sprintf( __( '%s is inactive.', 'automatewoo' ), AUTOMATEWOO_NAME ) . '</strong>';

		if ( version_compare( phpversion(), AUTOMATEWOO_MIN_PHP_VER, '<' ) ) {
			self::$errors[] = sprintf( __( '%1$s The plugin requires PHP version %2$s or newer.', 'automatewoo' ), $inactive_text, AUTOMATEWOO_MIN_PHP_VER );
			$passed         = false;
		} elseif ( ! self::is_woocommerce_version_ok() ) {
			self::$errors[] = sprintf( __( '%1$s The plugin requires WooCommerce version %2$s or newer.', 'automatewoo' ), $inactive_text, AUTOMATEWOO_MIN_WC_VER );
			$passed         = false;
		} elseif ( ! self::is_wp_version_ok() ) {
			self::$errors[] = sprintf( __( '%1$s The plugin requires WordPress version %2$s or newer.', 'automatewoo' ), $inactive_text, AUTOMATEWOO_MIN_WP_VER );
			$passed         = false;
		}

		return $passed;
	}

	/**
	 * Checks if the installed WooCommerce version is ok.
	 *
	 * @return bool
	 */
	public static function is_woocommerce_version_ok() {
		if ( ! function_exists( 'WC' ) ) {
			return false;
		}
		if ( ! AUTOMATEWOO_MIN_WC_VER ) {
			return true;
		}
		return version_compare( WC()->version, AUTOMATEWOO_MIN_WC_VER, '>=' );
	}

	/**
	 * Checks if the installed WordPress version is ok.
	 *
	 * @return bool
	 */
	public static function is_wp_version_ok() {
		global $wp_version;
		if ( ! AUTOMATEWOO_MIN_WP_VER ) {
			return true;
		}
		return version_compare( $wp_version, AUTOMATEWOO_MIN_WP_VER, '>=' );
	}

	/**
	 * Displays any errors as admin notices.
	 */
	public static function admin_notices() {
		if ( empty( self::$errors ) ) {
			return;
		}
		echo '<div class="notice notice-error"><p>';
		echo wp_kses_post( implode( '<br>', self::$errors ) );
		echo '</p></div>';
	}

}

AutomateWoo_Loader::init();
