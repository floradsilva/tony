<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Integrations
 */
class Integrations {

	/** @var Integration_Mailchimp */
	private static $mailchimp;

	/** @var Integration_ActiveCampaign */
	private static $activecampaign;

	/** @var Integration_Campaign_Monitor */
	private static $campaign_monitor;

	/** @var Integration_Twilio */
	private static $twilio;

	/** @var Integration_Bitly */
	private static $bitly;


	/**
	 * Is the WooCommerce Subscriptions plugin active?
	 *
	 * @since 4.5.0
	 *
	 * @param string $min_version
	 *
	 * @return bool
	 */
	static function is_subscriptions_active( $min_version = '2.2' ) {
		if ( ! class_exists( '\WC_Subscriptions' ) ) {
			return false;
		}
		return version_compare( \WC_Subscriptions::$version, $min_version, '>=' );
	}
	

	/**
	 * @return bool
	 */
	static function is_wpml() {
		return class_exists('SitePress');
	}


	/**
	 * @return bool
	 */
	static function is_woo_pos() {
		return class_exists('WC_POS');
	}



	/**
	 * @return bool
	 */
	static function is_memberships_enabled() {
		if ( ! function_exists( 'wc_memberships' ) ) return false;
		if ( version_compare( wc_memberships()->get_version(), '1.7', '<' ) ) return false;
		return true;
	}


	/**
	 * @return bool
	 */
	static function is_mc4wp() {
		return defined( 'MC4WP_VERSION' );
	}


	/**
	 * @return Integration_Twilio|false
	 */
	static function get_twilio() {
		if ( ! AW()->options()->twilio_integration_enabled ) {
			return false;
		}

		if ( ! isset( self::$twilio ) ) {
			self::$twilio = new Integration_Twilio(
				Clean::string( AW()->options()->twilio_from ),
				Clean::string( AW()->options()->twilio_auth_id ),
				Clean::string( AW()->options()->twilio_auth_token )
			);
		}
		return self::$twilio;
	}


	/**
	 * @return Integration_Bitly|false
	 */
	static function get_bitly() {
		if ( ! AW()->options()->bitly_api ) {
			return false;
		}
		if ( ! isset( self::$bitly ) ) {
			self::$bitly = new Integration_Bitly( Clean::string( AW()->options()->bitly_api ) );
		}
		return self::$bitly;
	}


	/**
	 * @return Integration_Mailchimp|false
	 */
	static function mailchimp() {
		if ( ! isset( self::$mailchimp ) ) {
			if ( Options::mailchimp_enabled() && Options::mailchimp_api_key() ) {
				self::$mailchimp = new Integration_Mailchimp( Options::mailchimp_api_key() );
			}
			else {
				self::$mailchimp = false;
			}
		}

		return self::$mailchimp;
	}


	/**
	 * @return Integration_ActiveCampaign
	 */
	static function activecampaign() {
		if ( ! isset( self::$activecampaign ) ) {
			$api_url = trim( Clean::string( AW()->options()->active_campaign_api_url ) );
			$api_key = trim( Clean::string( AW()->options()->active_campaign_api_key ) );
			self::$activecampaign = new Integration_ActiveCampaign( $api_url, $api_key );
		}

		return self::$activecampaign;
	}


	/**
	 * @return Integration_Campaign_Monitor
	 */
	static function campaign_monitor() {
		if ( ! isset( self::$campaign_monitor ) ) {
			$api_key = trim( Clean::string( AW()->options()->campaign_monitor_api_key ) );
			$client_id = trim( Clean::string( AW()->options()->campaign_monitor_client_id ) );
			self::$campaign_monitor = new Integration_Campaign_Monitor( $api_key, $client_id );
		}

		return self::$campaign_monitor;
	}

	/**
	 * is_points_rewards_active method.
	 *
	 * @since 4.5.0
	 *
	 * @param string $min_version
	 *
	 * @return bool
	 */
	static function is_points_rewards_active( $min_version = '1.6.15' ) {
		if ( ! class_exists( '\WC_Points_Rewards' ) ) {
			return false;
		}
		return version_compare( \WC_Points_Rewards::VERSION, $min_version, '>=' );
	}


	/**
	 * @deprecated in favour of Integrations::is_subscriptions_active()
	 *
	 * @return bool
	 */
	static function subscriptions_enabled() {
		return self::is_subscriptions_active();
	}

}


