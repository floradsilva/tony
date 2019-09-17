<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Performs licence functions for AutomateWoo and any add-ons.
 * Must be loaded before main admin class.
 *
 * @class Licenses
 */
class Licenses {


	/**
	 * @return array|false
	 */
	static function get_primary_license() {

		if ( ! $license = get_option( 'automatewoo_license' ) ) {
			return false;
		}

		$license = maybe_unserialize( base64_decode( $license ) );
		return $license;
	}


	/**
	 * Return licenses for all active add-ons
	 * @return array
	 */
	static function get_addon_licenses() {

		if ( ! $option = get_option( 'automatewoo_addon_licenses' ) ) {
			return [];
		}

		$licenses = [];

		foreach( Addons::get_all() as $addon ) {
			if ( isset( $option[ $addon->id ] ) ) {
				$licenses[ $addon->id ] = $option[ $addon->id ];
			}
		}

		return $licenses;
	}


	/**
	 * @param $product_id
	 * @return array|false
	 */
	static function get_license( $product_id ) {

		if ( self::is_primary( $product_id ) ) {
			return self::get_primary_license();
		}
		else {
			$licenses = self::get_addon_licenses();

			if ( ! isset( $licenses[ $product_id ] ) )
				return false;

			return $licenses[ $product_id ];
		}
	}


	/**
	 * @param $product_id
	 * @return bool
	 */
	static function is_primary( $product_id ) {
		return $product_id == AW()->plugin_slug;
	}


	/**
	 * Returns true if license exists i.e. is active or expired.
	 *
	 * @param string $app_id String app ID or plugin slug, defaults to AutomateWoo's plugin slug.
	 *
	 * @return bool
	 */
	static function is_valid( $app_id = null ) {
		if ( ! $app_id ) {
			$app_id = AW()->plugin_slug;
		}

		return self::get_license( $app_id ) ? true : false;
	}

	/**
	 * Get the license status of specific app.
	 *
	 * @param string $app_id String app ID or plugin slug.
	 *
	 * @return bool
	 */
	static function get_status( $app_id ) {
		$license = self::get_license( $app_id );

		if ( ! $license || ! isset( $license['status'] ) ) {
			return false;
		}

		return $license['status'];
	}


	/**
	 * @param string $app_id
	 *
	 * @return bool
	 */
	static function is_expired( $app_id ) {
		$status = self::get_status( $app_id );

		// return false if there is no status so we don't break legacy installs that have no status
		if ( ! $status ) {
			return false;
		}

		return $status === 'expired';
	}


	/**
	 * @param string $product_id
	 * @param string $license_key
	 * @param string $license_status
	 */
	static function update( $product_id, $license_key, $license_status ) {

		if ( self::is_primary( $product_id ) ) {
			$license = [
				'key'    => $license_key,
				'url'    => self::get_domain(),
				'status' => $license_status
			];

			$license = base64_encode( maybe_serialize($license) );
			update_option( 'automatewoo_license', $license );
		}
		else {
			$licenses = self::get_addon_licenses();

			$licenses[ $product_id ] = [
				'key'    => $license_key,
				'status' => $license_status
			];

			update_option( 'automatewoo_addon_licenses', $licenses, true );
		}
	}


	/**
	 * Remove the licence
	 * @param $product_id
	 */
	static function remove( $product_id ) {

		if ( self::is_primary( $product_id ) ) {
			delete_option('automatewoo_license');
		}
		else {
			$licenses = self::get_addon_licenses();
			unset( $licenses[$product_id] );
			update_option( 'automatewoo_addon_licenses', $licenses, true );
		}
	}


	/**
	 *
	 */
	static function remove_all() {
		delete_option( 'automatewoo_license' );
		delete_option( 'automatewoo_addon_licenses' );
	}


	/**
	 *
	 */
	static function check_for_domain_mismatch() {

		$license = self::get_primary_license();

		if ( ! $license )
			return;

		$actual_url = self::remove_domain_www_prefix( self::get_domain() );
		$activation_url = self::remove_domain_www_prefix( $license['url'] );

		if ( ! $actual_url || ! $activation_url )
			return;

		if ( $activation_url != $actual_url ) {
			// remove and but don't deactivate, install may have been cloned
			self::remove_all();
		}
	}


	/**
	 * Checks the status of plugin and add-ons
	 * @param bool $force - Override limiting to once every 4 days
	 */
	static function maybe_check_status( $force = false ) {

		if ( defined( 'IFRAME_REQUEST' ) || is_ajax() ) {
			return;
		}

		if ( ! $force ) {
			$frequency = DAY_IN_SECONDS * 4;
			$last_checked = get_option( 'automatewoo_license_status_last_checked' );

			if ( $last_checked && $last_checked > time() - $frequency ) {
				return;
			}
		}

		self::remote_check_status();

		update_option( 'automatewoo_license_status_last_checked', time(), false );
	}


	/**
	 * Use to trigger a status check in the next request
	 */
	static function reset_status_check_timer() {
		update_option( 'automatewoo_license_status_last_checked', '', false );
	}


	/**
	 * @param int $time defaults to 5 minutes
	 */
	static function schedule_reset_status_check_timer( $time = 300 ) {
		wp_clear_scheduled_hook( 'automatewoo_license_reset_status_check_timer' );
		wp_schedule_single_event( time() + $time, 'automatewoo_license_reset_status_check_timer' );
	}


	/**
	 * Checks the status of licence and performs any required actions based on the status
	 *
	 * @todo check dev status
	 */
	private static function remote_check_status() {

		$apps = [];

		if ( $license = self::get_primary_license() ) {
			$apps[] = [
				'id' => AW()->plugin_slug,
				'key' => $license['key'],
				'version' => AW()->version
			];
		}

		foreach ( Addons::get_all() as $addon ) {
			if ( $license = self::get_license( $addon->id ) ) {
				$apps[] = [
					'id' => $addon->id,
					'key' => $license['key'],
					'version' => $addon->version
				];
			}
		}

		// no apps need checking
		if ( empty( $apps ) ) {
			return false;
		}

		global $wp_version;

		$request_args = [
			'domain' => self::get_domain(),
			'wp_version' => $wp_version,
			'wc_version' => WC()->version,
			'locale' => get_locale(),
			'apps' => $apps
		];

		if ( class_exists( 'WC_Subscriptions' ) ) {
			$request_args[ 'wc_subscriptions_version' ] = \WC_Subscriptions::$version;
		}

		if ( function_exists( 'wc_memberships' ) ) {
			$request_args[ 'wc_memberships_version' ] = wc_memberships()->get_version();
		}

		$response = self::remote_get( 'multi_app_status_check', $request_args );

		if ( ! $response || ! $response->success ) {
			return false;
		}

		if ( $response->apps ) foreach ( $response->apps as $app_id => $app_response ) {
			$app_id            = Clean::string( $app_id );
			$activation_status = isset( $app_response->activation_status ) ? Clean::string( $app_response->activation_status ) : 'invalid';
			$license_status    = isset( $app_response->license_status ) ? Clean::string( $app_response->license_status ) : 'invalid';

			if ( ! $license = self::get_license( $app_id ) ) {
				continue;
			}

			switch ( $activation_status ) {
				case 'valid':
				case 'valid-dev':
				case 'expired':
					self::update( $app_id, $license['key'], $license_status );
					break;

				case 'deactivated':
				case 'invalid':
					self::remove( $app_id );
					break;
			}
		}
	}


	/**
	 * Remotely activate a license
	 * Sets updates the license option if license is activate.
	 *
	 * @param $product_id string
	 * @param $license_key string
	 *
	 * @return \WP_Error|string
	 */
	static function remote_activate( $product_id, $license_key ) {

		$response = self::remote_get( 'activation', [
			'app_id' => $product_id,
			'license_key' => $license_key,
			'domain' => self::get_domain()
		]);

		if ( ! $response ) {
			return new \WP_Error( 2, __( 'No response received from server.', 'automatewoo' ) );
		}

		if ( isset( $response->error ) ) {
			return new \WP_Error( '1', Clean::string( $response->error ) );
		}

		if ( isset( $response->activated ) ) {
			$license_status = isset( $response->license_status ) ? Clean::string( $response->license_status ) : 'invalid';

			self::update( $product_id, $license_key, $license_status );
			return Clean::string( $response->message );
		}

		return new \WP_Error( 2, __( 'Invalid response received from server.', 'automatewoo' ) );
	}


	/**
	 * @param $product_id
	 */
	static function remote_deactivate( $product_id ) {

		if ( self::is_primary( $product_id ) ) {

			$license = self::get_primary_license();

			if ( $license ) {
				// Attempt deactivation
				self::remote_get( 'deactivation', [
					'license_key' => $license['key'],
					'domain' => $license['url']
				]);
			}
		}
		else {
			$license_info = self::get_license( $product_id );

			if ( $license_info ) {
				// Attempt deactivation
				self::remote_get( 'deactivation', [
					'app_id' => $product_id,
					'license_key' => $license_info['key'],
					'domain' => self::get_domain()
				]);
			}
		}

		self::remove( $product_id ); // remove anyway
	}



	/**
	 * @return bool|\WP_Error
	 */
	static function is_valid_dev_domain() {

		if ( $cache = Cache::get_transient( 'is_dev_domain' ) ) {
			return $cache === 'yes';
		}

		$check = self::remote_check_dev_domain();

		if ( is_wp_error( $check ) )
			return $check;

		Cache::set_transient( 'is_dev_domain', $check ? 'yes' : 'no' );

		return $check;
	}



	/**
	 * @return bool|\WP_Error
	 */
	static function remote_check_dev_domain() {

		$response = self::remote_get( 'is_dev_domain', [
			'domain' => self::get_domain(),
		]);

		if ( $response && $response->success ) {
			return (bool) $response->is_dev;
		}
		else {
			return new \WP_Error( 1, __( 'Could not connect to remote server to check domain status.', 'automatewoo' ) );
		}
	}


	/**
	 * @return string
	 */
	static function get_domain() {
		$url = site_url();
		$url = str_replace( [ 'http://', 'https://' ], '', $url );
		$url = untrailingslashit( $url );
		return $url;
	}


	/**
	 * @param $domain
	 * @return string
	 */
	static function remove_domain_www_prefix( $domain ) {
		if ( substr( $domain, 0, 4 ) === 'www.' ) {
			$domain = substr( $domain, 4 );
		}
		return $domain;
	}


	/**
	 * @param string|bool $utm_source
	 * @return string
	 */
	static function get_renewal_url( $utm_source = false ) {
		return Admin::get_website_link( 'account', $utm_source );
	}


	/**
	 * @return bool
	 */
	static function has_expired_products() {

		if ( self::is_expired( AW()->plugin_slug ) )
			return true;

		$addons = self::get_addon_licenses();

		foreach ( $addons as $id => $addon ) {
			if ( self::is_expired( $id ) )
				return true;
		}

		return false;
	}


	/**
	 * @return bool
	 */
	static function has_unactivated_products() {

		if ( ! self::is_valid( AW()->plugin_slug ) )
			return true;

		$addons = Addons::get_all();

		foreach ( $addons as $id => $addon ) {
			if ( ! self::is_valid( $id ) )
				return true;
		}

		return false;
	}


	/**
	 * @param $request
	 * @param $args
	 * @return object|false
	 */
	static function remote_get( $request, $args = [] ) {

		$base = [
			'wc-api' => 'licences',
			'app_id' => AW()->plugin_slug
		];

		$base['request'] = $request;
		$args = array_merge( $base, $args );

		$request_url = add_query_arg( $args, AW()->website_url );

		$response = wp_safe_remote_get( $request_url, [
			'timeout' => 20,
			'sslverify' => false
		] );

		if ( is_wp_error( $response ) ) {
			Logger::error( 'license-api', $request . ': ' . $response->get_error_message() );
			return false;
		}

		if ( ! isset( $response['body'] ) ) {
			return false;
		}

		return json_decode( $response['body'] );
	}

}
