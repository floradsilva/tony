<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class Events_Runner_Async_Request.
 * HTTP request to run a set of async events.
 *
 * @since 3.8
 */
class Events_Runner_Async_Request extends Async_Request_Abstract {

	/** @var string */
	protected $action = 'events_starter';

	/** @var int */
	public $max_events_to_process_at_once;


	public function __construct() {
		$this->max_events_to_process_at_once = apply_filters( 'automatewoo/events_runner_async_request/max_at_once', 4 );
		parent::__construct();
	}


	protected function handle() {
		$event_ids = Clean::ids( $this->get_raw_request_data() );

		if ( empty( $event_ids ) ) {
			return;
		}

		$events_to_run_now = array_slice( $event_ids, 0, $this->max_events_to_process_at_once );
		$events_remaining = array_slice( $event_ids, $this->max_events_to_process_at_once );

		// run the first 4 events right now
		$this->run_events_now( $events_to_run_now );

		// if there are events left over start the background processor
		if ( $events_remaining ) {
			$this->dispatch_events_background_processor( $events_remaining );
		}
	}


	/**
	 * @param array $event_ids
	 */
	public function run_events_now( $event_ids ) {
		foreach( $event_ids as $event_id ) {
			if ( $event = Event_Factory::get( Clean::id( $event_id ) ) ) {
				$event->run();
			}
		}
	}


	/**
	 * @param array $event_ids
	 */
	public function dispatch_events_background_processor( $event_ids ) {
		/** @var Background_Processes\Event_Runner $process */
		$process = Background_Processes::get('events');

		if ( $process->has_queued_items() ) {
			// if processor is already running, don't start a new one

			// removes the 3 minute delay on the events so they will be process in the next events batch
			$now = aw_normalize_date( 'now' );

			foreach( $event_ids as $event_id ) {
				if ( $event = Event_Factory::get( Clean::id( $event_id ) ) ) {
					$event->set_date_scheduled( $now );
					$event->save();
				}
			}
		}
		else {
			$process->data( $event_ids )->start();
		}
	}

}
