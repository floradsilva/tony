<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Contains functions relevant to open, click, conversion tracking.
 *
 * @since 3.9
 */
class Tracking {


	/**
	 * @param Workflow $workflow
	 * @return string
	 */
	static function get_open_tracking_url( $workflow ) {
		$log = $workflow->get_current_log();

		$url = add_query_arg([
			'aw-action' => 'open',
			'log' => $log ? $log->get_id() : 0
		], home_url() );

		return apply_filters( 'automatewoo_open_track_url', $url, $workflow );
	}


	/**
	 * @param Workflow $workflow
	 * @param $redirect
	 * @return string
	 */
	static function get_click_tracking_url( $workflow, $redirect ) {
		$valid_redirect = wp_validate_redirect( $redirect );

		if ( ! $valid_redirect ) {
			return $redirect; // if redirect is not a valid redirect return the original URL
		}

		$log = $workflow->get_current_log();

		$args = [
			'aw-action' => 'click',
			'log' => $log ? $log->get_id() : 0,
			'redirect' => urlencode( $valid_redirect )
		];

		$url = add_query_arg( $args, home_url() );

		return apply_filters( 'automatewoo_click_track_url', $url, $args );
	}


	/**
	 * Records the open track event if a valid log id is passed.
	 * Then outputs a blank GIF image.
	 */
	static function handle_open_tracking_url() {
		$log_id = Clean::id( aw_request( 'log' ) );

		if ( $log = AW()->get_log( $log_id ) ) {
			$log->record_open();
		}

		$image_path = AW()->admin_path( '/assets/img/blank.gif' );

		// render image
		header( 'Content-Type: image/gif' );
		header( 'Pragma: public' ); // required
		header( 'Expires: 0' ); // no cache
		header( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
		header( 'Cache-Control: private', false );
		header( 'Content-Disposition: attachment; filename="blank.gif"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . filesize( $image_path ) ); // provide file size
		readfile( $image_path );
		exit;
	}


	/**
	 * Records the click event and then redirects the user if safe.
	 * Still allow redirect if log param is invalid, when testing a '0' value for log is used.
	 */
	static function handle_click_tracking_url() {
		$redirect = esc_url_raw( aw_request( 'redirect' ) );
		$log_id = Clean::id( aw_request( 'log' ) );

		if ( ! $redirect ) {
			return;
		}

		if ( $log = AW()->get_log( $log_id ) ) {
			$log->record_click( $redirect );
		}

		// fallback to the home page instead of the admin area if redirect is unsafe
		add_filter( 'wp_safe_redirect_fallback', [ 'AutomateWoo\Tracking', 'safe_redirect_fallback' ] );

		wp_safe_redirect( $redirect );
		exit;
	}


	/**
	 * @return string
	 */
	static function safe_redirect_fallback() {
		return apply_filters( 'automatewoo/click_track/safe_redirect_fallback', home_url() );
	}


}
