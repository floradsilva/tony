<?php
// phpcs:ignoreFile

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Logger;

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'AW_WP_Async_Request', false ) ) {
	require_once AW()->lib_path( '/wp-async-request.php' );
}

if ( ! class_exists( 'AW_WP_Background_Process', false ) ) {
	require_once AW()->lib_path( '/wp-background-process.php' );
}

/**
 * Base background process class
 */
abstract class Base extends \AW_WP_Background_Process {

	/** @var string */
	public $action;


	/**
	 * Background Processor base constructor.
	 * Deliberately doesn't call parent method.
	 */
	public function __construct() {
		$this->prefix                   = is_multisite() ? 'aw_' . get_current_blog_id() : 'aw';
		$this->identifier               = $this->prefix . '_' . $this->action;
		$this->cron_hook_identifier     = $this->identifier . '_background_process_cron';
		$this->cron_interval_identifier = $this->identifier . '_cron_interval';

		add_action( 'wp_ajax_' . $this->get_ajax_action(), [ $this, 'maybe_handle' ] );
		add_action( 'wp_ajax_nopriv_' . $this->get_ajax_action(), [ $this, 'maybe_handle' ] );
		add_action( $this->cron_hook_identifier, [ $this, 'handle_cron_healthcheck' ] );
		add_filter( 'cron_schedules', [ $this, 'schedule_cron_healthcheck' ] );
	}


	/**
	 * @return boolean
	 */
	public function has_queued_items() {
		return false === $this->is_queue_empty();
	}


	/**
	 * Use this instead of dispatch to start process
	 * @return bool|\WP_Error
	 */
	public function start() {
		if ( empty( $this->data ) ) {
			$this->log( 'Started process but there were no items.' );
			return false;
		}

		$count = count( $this->data );
		$this->save();
		$dispatched = $this->dispatch();

		if ( is_wp_error( $dispatched ) ) {
			$this->log( sprintf( 'Unable to start process: %s', $dispatched->get_error_message() ) );
		}
		else {
			$this->log( sprintf( 'Process started for %s items.', $count ) );
		}

		return $dispatched;
	}


	/**
	 * Process completed
	 */
	protected function complete() {
		$this->log( 'Process completed.' );
		parent::complete();
	}


	/**
	 * Reduce time limit to 10s
	 * @return bool
	 */
	protected function time_exceeded() {
		$finish = $this->start_time + apply_filters( 'automatewoo/background_process/time_limit', 10 ); // 10 seconds
		$return = false;

		if ( time() >= $finish ) {
			$return = true;
		}

		return $return;
	}


	/**
	 * Reduce memory limit
	 * @return bool
	 */
	protected function memory_exceeded() {

		// use only 40% of max memory
		$memory_limit_percentage = apply_filters( 'automatewoo/background_process/memory_limit_percentage', 0.4 );

		$memory_limit = $this->get_memory_limit() * $memory_limit_percentage;
		$current_memory = memory_get_usage( true );
		$return = false;

		if ( $current_memory >= $memory_limit ) {
			$return = true;
		}

		return $return;
	}


	/**
	 * Handle
	 *
	 * Pass each queue item to the task handler, while remaining
	 * within server memory and time limit constraints.
	 */
	protected function handle() {

		do_action( 'automatewoo/background_process/before_handle', $this );

		$this->lock_process();

		do {
			$batch = $this->get_batch();

			foreach ( $batch->data as $key => $value ) {
				$task = $this->task( $value );

				if ( false !== $task ) {
					$batch->data[ $key ] = $task;
				} else {
					unset( $batch->data[ $key ] );
				}

				if ( $this->time_exceeded() || $this->memory_exceeded() ) {
					// Batch limits reached.
					break;
				}
			}

			// Update or delete current batch.
			if ( ! empty( $batch->data ) ) {
				$this->update( $batch->key, $batch->data );
			} else {
				$this->delete( $batch->key );
			}
		} while ( ! $this->time_exceeded() && ! $this->memory_exceeded() && ! $this->is_queue_empty() );

		// throttle process here with sleep to try and prevent crashing mysql
		$sleep_seconds = apply_filters( 'automatewoo/background_process/post_batch_sleep', 1, $this );

		if ( $sleep_seconds ) {
			sleep( $sleep_seconds );
		}

		$this->unlock_process();

		// Start next batch or complete process.
		if ( ! $this->is_queue_empty() ) {
			$this->dispatch();
		} else {
			$this->complete();
		}
	}


	/**
	 * @param $message
	 */
	public function log( $message ) {
		Logger::info( 'background-process', $this->action. ': ' . $message );
	}


	/**
	 * over-ridden due to issue https://github.com/A5hleyRich/wp-background-processing/issues/7
	 *
	 * this method actually creates a new batch rather it doesn't replace existing queued items
	 *
	 * @return $this
	 */
	public function save() {
		$key = $this->generate_key();

		if ( ! empty( $this->data ) ) {
			update_site_option( $key, $this->data );
		}

		$this->data = [];
		return $this;
	}


	/**
	 * Dispatch background process.
	 *
	 * @return array|\WP_Error
	 */
	public function dispatch() {
		// Schedule the cron healthcheck.
		$this->schedule_event();

		// Perform remote post.
		$request = wp_remote_post( esc_url_raw( $this->get_post_url() ), $this->get_post_args() );

		if ( AUTOMATEWOO_BACKGROUND_PROCESS_DEBUG && is_wp_error( $request ) ) {
			Logger::error( 'background-process-debug', 'Dispatch: ' . $request->get_error_message() );
		}

		return $request;
	}


	/**
	 * Schedule health check if not scheduled
	 */
	public function maybe_schedule_health_check() {
		$this->schedule_event();
	}


	/**
	 * Replace parent method to schedule health check 1 minute from now.
	 * This is preferred rather than setting the health check to run instantly.
	 * This value could actually be set for longer but if there is an issue with the HTTP request
	 * this acts as a fallback method of processing events.
	 *
	 * @since 4.4
	 */
	protected function schedule_event() {
		if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
			wp_schedule_event( time() + 60, $this->cron_interval_identifier, $this->cron_hook_identifier );
		}
	}


	/**
	 * @return array
	 */
	function get_data() {
		return $this->data;
	}


	/**
	 * @return \stdClass
	 */
	function get_next_batch() {
		return $this->get_batch();
	}


	/**
	 * Get query args
	 *
	 * @return array
	 */
	protected function get_query_args() {
		if ( property_exists( $this, 'query_args' ) ) {
			return $this->query_args;
		}

		return array(
			'action' => $this->get_ajax_action(),
			'nonce'  => wp_create_nonce( $this->identifier ),
		);
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

		$args = [];

		if ( ! AUTOMATEWOO_BACKGROUND_PROCESS_DEBUG ) {
			$args['timeout'] = 0.01;
			$args['blocking'] = false;
		}
		else {
			$args['timeout'] = 30;
		}

		$args['body'] = json_encode( $this->data );
		$args['cookies'] = $_COOKIE;
		$args['sslverify'] = apply_filters( 'https_local_ssl_verify', false );

		return apply_filters( 'automatewoo/background_process/post_args', $args, $this );
	}


	/**
	 * Get dispatch URL for HTTP post.
	 *
	 * @since 4.4.0
	 *
	 * @return string
	 */
	public function get_post_url() {
		$url = add_query_arg( $this->get_query_args(), $this->get_query_url() );
		return apply_filters( 'automatewoo/background_process/post_url', $url, $this );
	}


	/**
	 * @return string
	 */
	public function get_ajax_action() {
		return $this->identifier . '_background_process';
	}

}
