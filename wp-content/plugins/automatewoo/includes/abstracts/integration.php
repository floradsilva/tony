<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Abstract for API integration classes.
 *
 * @class Integration
 * @since 2.3
 */
abstract class Integration {

	/** @var string */
	public $integration_id;

	/** @var bool */
	public $log_errors = true;


	/**
	 * @param $message
	 */
	public function log( $message ) {
		if ( ! $this->log_errors ) {
			return;
		}

		Logger::info( 'integration-' . $this->integration_id, $message );
	}


	/**
	 * @param Remote_Request $request
	 */
	public function maybe_log_request_errors( $request ) {
		if ( ! $this->log_errors ) {
			return;
		}

		if ( $request->is_http_error() ) {
			$this->log( $request->get_http_error_message() );
		}
		elseif ( $request->is_api_error() ) {
			$this->log(
				$request->get_response_code() . ' ' . $request->get_response_message()
				. '. Method: ' . $request->method
				. '. Endpoint: ' . $request->url
				. '. Response body: ' . print_r( $request->get_body(), true )
			);
		}
	}

}
