<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Integration_Bitly
 * @since 3.9
 */
class Integration_Bitly extends Integration {

	/** @var string */
	public $integration_id = 'bitly';

	/** @var string */
	private $api_key;

	/** @var string  */
	private $api_base_url;


	/**
	 * @param string $api_key
	 */
	function __construct( $api_key ) {
		$this->api_key = $api_key;
		$this->api_base_url = 'https://api-ssl.bitly.com/v3';
	}


	/**
	 * @param string $long_url
	 * @param bool $ignore_cache
	 * @return string|false
	 */
	function shorten_url( $long_url, $ignore_cache = false ) {
		$cache_key = md5( $long_url );

		if ( ! $ignore_cache && $cache = Cache::get( $cache_key, 'bitly' ) ) {
			return $cache;
		}

		$request = $this->request('GET', '/shorten', [
			'longUrl' => esc_url_raw( $long_url ),
		]);

		if ( ! $request->is_successful() ) {
			return false;
		}

		$body = $request->get_body();
		$short_url = esc_url_raw( $body['data']['url'] );

		Cache::set( $cache_key, $short_url, 'bitly' );

		return apply_filters( 'automatewoo/bitly/shorten_url', $short_url, $long_url );
	}


	/**
	 * @param string $text
	 * @return string
	 */
	function shorten_urls_in_text( $text ) {
		$replacer = new Replace_Helper( $text, [ $this, 'shorten_url' ], 'text_urls' );
		return $replacer->process();
	}


	/**
	 * @param $method
	 * @param $endpoint
	 * @param $args
	 *
	 * @return Remote_Request|false
	 */
	function request( $method, $endpoint, $args = [] ) {
		$request_args = [
			'timeout' => 10,
			'method' => $method,
			'sslverify' => false
		];

		$args['access_token'] = $this->api_key;
		$url = $this->api_base_url . $endpoint;

		switch ( $method ) {
			case 'GET':
				$url = add_query_arg( array_map( 'urlencode', $args ), $url );
				break;

			default:
				return false;
				break;
		}

		$request = new Remote_Request( $url, $request_args );

		$this->maybe_log_request_errors( $request );

		return $request;
	}


}
