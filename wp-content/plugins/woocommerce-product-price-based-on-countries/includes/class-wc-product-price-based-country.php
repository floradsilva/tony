<?php
/**
 * WooCommerce Price Based on Country main class
 *
 * @package WCPBC
 * @version 1.8.2
 */

defined( 'ABSPATH' ) || exit;

/**
 * WC_Product_Price_Based_Country Class
 */
class WC_Product_Price_Based_Country {

	/**
	 * Product Price Based Country version
	 *
	 * @var string
	 */
	public $version = '1.8.11';

	/**
	 * The front-end pricing zone
	 *
	 * @var WCPBC_Pricing_Zone
	 */
	public $current_zone = null;

	/**
	 * Min WC required version.
	 *
	 * @var string
	 */
	protected $min_wc_version = '2.6';

	/**
	 * Enviroment alert
	 *
	 * @var string
	 */
	protected $environment_alert = '';

	/**
	 * The single instance of the class.
	 *
	 * @var WC_Product_Price_Based_Country
	 */
	protected static $_instance = null;

	/**
	 * Main WC_Product_Price_Based_Country Instance
	 *
	 * @static
	 * @see WCPBC()
	 * @return WC_Product_Price_Based_Country
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Get the plugin url.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return plugin_dir_url( WCPBC_PLUGIN_FILE );
	}

	/**
	 * Get the plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return plugin_dir_path( WCPBC_PLUGIN_FILE );
	}

	/**
	 * Return the plugin base name
	 *
	 * @return string
	 * @since 1.7.4
	 */
	public function plugin_basename() {
		return plugin_basename( WCPBC_PLUGIN_FILE );
	}

	/**
	 * WC_Product_Price_Based_Country Constructor.
	 */
	public function __construct() {
		$this->includes();

		register_activation_hook( WCPBC_PLUGIN_FILE, array( 'WCPBC_Install', 'install' ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WCPBC_PLUGIN_FILE ), array( $this, 'plugin_action_links' ) );
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'init_plugin' ), 5 );
	}

	/**
	 * Include required files used in admin and on the frontend.
	 */
	private function includes() {

		include_once $this->plugin_path() . 'includes/class-wcpbc-pricing-zone.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-pricing-zones.php';
		include_once $this->plugin_path() . 'includes/wcpbc-core-functions.php';
		include_once $this->plugin_path() . 'includes/wcpbc-metadata-functions.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-integrations.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-frontend.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-frontend-pricing.php';
		include_once $this->plugin_path() . 'includes/class-wcpbc-ajax-geolocation.php';

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			include_once $this->plugin_path() . 'includes/class-wcpbc-install.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-notices.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-meta-boxes.php';
			include_once $this->plugin_path() . 'includes/admin/class-wcpbc-admin-ads.php';
		}

		if ( ( defined( 'DOING_CRON' ) || is_admin() ) && 'yes' === get_option( 'wc_price_based_country_allow_tracking', 'no' ) ) {
			include_once $this->plugin_path() . 'includes/class-wcpbc-tracker.php';
		}
	}

	/**
	 * Localisation
	 *
	 * @since 1.6.3
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wc-price-based-country', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @since 1.6.11
	 * @param mixed $links Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {

		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=price-based-country' ) . '" aria-label="' . esc_attr__( 'View Price Based on Country settings', 'wc-price-based-country' ) . '">' . esc_html__( 'Settings', 'wc-price-based-country' ) . '</a>',
		);

		if ( ! wcpbc_is_pro() ) {
			$action_links['get-pro'] = '<a target="_blank" rel="noopener noreferrer" style="color:#46b450;" href="https://www.pricebasedcountry.com/pricing/?utm_source=action-link&utm_medium=banner&utm_campaign=Get_Pro" aria-label="' . esc_attr__( 'Get Price Based on Country Pro', 'wc-price-based-country' ) . '">' . esc_html__( 'Get Pro', 'wc-price-based-country' ) . '</a>';
		}

		return array_merge( $action_links, $links );
	}

	/**
	 * Show row meta on the plugin screen.
	 *
	 * @since 1.7.0
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( WCPBC_PLUGIN_FILE ) === $file ) {
			$row_meta = array(
				'docs' => '<a href="' . esc_url( 'https://www.pricebasedcountry.com/docs/?utm_source=row-meta&utm_medium=banner&utm_campaign=Docs' ) . '" aria-label="' . esc_attr__( 'View documentation', 'wc-price-based-country' ) . '">' . esc_html__( 'Docs', 'wc-price-based-country' ) . '</a>',
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	/**
	 * Checks the environment for compatibility problems.
	 *
	 * @return boolean
	 */
	private function check_environment() {
		if ( ! defined( 'WC_VERSION' ) ) {
			// translators: HTML Tags.
			$this->environment_alert = sprintf( __( '%1$sPrice Based on Country%2$s requires WooCommerce to be activated to work. Learn how to install Price Based on Country in the %3$sGetting Started Guide%4$s.', 'wc-price-based-country' ), '<strong>', '</strong>', '<a href="https://www.pricebasedcountry.com/docs/getting-started/">', '</a>' );
			return false;
		}

		if ( version_compare( WC_VERSION, $this->min_wc_version, '<' ) ) {
			// translators: HTML Tags.
			$this->environment_alert = sprintf( __( 'Price Based on Country - The minimum WooCommerce version required for this plugin is %1$s. You are running %2$s.', 'wc-price-based-country' ), $this->min_wc_version, WC_VERSION );
			return false;
		}

		return true;
	}

	/**
	 * Init plugin
	 *
	 * @since 1.8.0
	 */
	public function init_plugin() {

		if ( ! $this->check_environment() ) {
			add_action( 'admin_notices', array( $this, 'environment_notice' ) );
			return;
		}

		if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
			// Admin request.
			WCPBC_Install::init();
			WCPBC_Admin::init();
			WCPBC_Admin_Meta_Boxes::init();
			WCPBC_Admin_Notices::init();
			WCPBC_Admin_Ads::init();
		}

		WCPBC_Frontend::init();
		WCPBC_Ajax_Geolocation::init();

		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
		add_action( 'woocommerce_init', array( $this, 'frontend_init' ), 999 );
		add_action( 'init', array( $this, 'ajax_frontend_init' ), 999 );

	}

	/**
	 * Display the environment alert
	 */
	public function environment_notice() {
		echo '<div id="message" class="error"><p>' . wp_kses_post( $this->environment_alert ) . '</p></div>';
	}

	/**
	 * Register Widgets
	 *
	 * @since 1.5.0
	 */
	public function register_widgets() {
		if ( class_exists( 'WC_Widget' ) ) {
			include_once $this->plugin_path() . 'includes/class-wcpbc-widget-country-selector.php';
			register_widget( 'WCPBC_Widget_Country_Selector' );
		}
	}

	/**
	 * Init front-end
	 */
	public function frontend_init() {

		if ( ! $this->is_request( 'frontend' ) || apply_filters( 'wc_price_based_country_stop_pricing', false ) ) {
			// Do only if woocommerce frontend have been loaded.
			return;
		}

		do_action( 'wc_price_based_country_before_frontend_init' );

		// Set the current zone.
		$this->current_zone = wcpbc_get_zone_by_country();

		// Init frontend pricing.
		WCPBC_Frontend_Pricing::init();

		do_action( 'wc_price_based_country_frontend_init' );
	}

	/**
	 * What type of request is this?
	 *
	 * @param string $type frontend or admin.
	 * @return bool
	 */
	private function is_request( $type ) {

		$ajax_action = defined( 'DOING_AJAX' ) && DOING_AJAX && isset( $_REQUEST['action'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) : false; // WPCS: CSRF ok.

		switch ( $type ) {
			case 'frontend':
				return wcpbc_is_woocommerce_frontend() && ! defined( 'DOING_CRON' ) && ( ! is_admin() || ( $ajax_action && apply_filters( 'wc_price_based_country_is_ajax_frontend', has_action( 'wp_ajax_nopriv_' . $ajax_action ), $ajax_action ) ) );

			case 'admin':
				return ! defined( 'DOING_CRON' ) && ! $this->is_request( 'frontend' );
		}
	}

	/**
	 * Init front-end on AJAX calls. Improve compatibility with plugins which adds the "wp_ajax_nopriv_..." action on the 'init' hook.
	 */
	public function ajax_frontend_init() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ! did_action( 'wc_price_based_country_frontend_init' ) ) {
			$this->frontend_init();
		}
	}

	/**
	 * Get regions.
	 *
	 * @deprecated 1.8.0 No longer needed.
	 * @return array
	 */
	public function get_regions() {
		_deprecated_function( 'WC_Product_Price_Based_Country::get_regions', '1.8.0', 'WCPBC_Pricing_Zones::get_zones' );
		return array();
	}
}
