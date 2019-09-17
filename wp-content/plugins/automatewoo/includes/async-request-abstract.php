<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'AW_WP_Async_Request', false ) ) {
	require_once AW()->lib_path( '/wp-async-request.php' );
}

/**
 * Class Async_Request_Abstract.
 * Adds some simple functionality on top of WP_Async_Request.
 *
 * @since 3.8
 */
abstract class Async_Request_Abstract extends \AW_WP_Async_Request {


	public function __construct() {
		$this->prefix = is_multisite() ? 'aw_' . get_current_blog_id() : 'aw';
		parent::__construct();
	}


	/**
	 * Get post args
	 *
	 * @return array
	 */
	protected function get_post_args() {
		if ( property_exists( $this, 'post_args' ) ) {
			return $this->post_args;
		}

		$args = [
			'body' => json_encode( $this->data ),
			'cookies' => $_COOKIE,
			'sslverify' => apply_filters( 'https_local_ssl_verify', false ),
		];

		if ( ! AUTOMATEWOO_BACKGROUND_PROCESS_DEBUG ) {
			$args['timeout'] = 0.01;
			$args['blocking'] = false;
		}
		else {
			$args['timeout'] = 30;
		}

		return $args;
	}


	/**
	 * @return array
	 */
	function get_raw_request_data() {
		global $HTTP_RAW_POST_DATA;
		if ( ! isset( $HTTP_RAW_POST_DATA ) ) {
			$HTTP_RAW_POST_DATA = trim( file_get_contents( 'php://input' ) );
		}
		return json_decode( $HTTP_RAW_POST_DATA, true);
	}


}