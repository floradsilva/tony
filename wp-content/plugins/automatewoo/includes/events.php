<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Events
 * @since 3.4.0
 */
class Events {

	/** @var Events_Runner_Async_Request  */
	private static $events_runner_async_request;

	/** @var array of event IDs */
	static $events_runner_async_request_data = [];

	/** @var array Store of events created in the current request */
	static $events_created_in_request = [];


	/**
	 * @return Events_Runner_Async_Request
	 */
	static function get_event_runner_async_request() {
		if ( ! isset( self::$events_runner_async_request ) ) {
			self::$events_runner_async_request = new Events_Runner_Async_Request();
		}
		return self::$events_runner_async_request;
	}


	/**
	 * @return int
	 */
	static function get_batch_size() {
		return (int) apply_filters( 'automatewoo/events/batch_size', 150 );
	}


	/**
	 * Check for events due to be run
	 */
	static function run_due_events() {

		/** @var Background_Processes\Event_Runner $process */
		$process = Background_Processes::get('events');

		// don't start a new process until the previous is finished
		if ( $process->has_queued_items() ) {
			$process->maybe_schedule_health_check();
			return;
		}

		$query = ( new Event_Query() )
			->set_limit( self::get_batch_size() )
			->set_ordering( 'date_scheduled', 'ASC' )
			->where( 'date_scheduled', new DateTime(), '<' )
			->where( 'status', 'pending' )
			->set_return( 'ids' );

		if ( ! $events = $query->get_results() ) {
			return;
		}

		$process->data( $events )->start();
	}


	/**
	 * Schedule async event.
	 *
	 * If $unique_for_request is set to true, multiple identical events will be blocked from creation in the same request.
	 * The database won't be queried to determine if the event is unique.
	 *
	 * If AUTOMATEWOO_ENABLE_INSTANT_EVENT_DISPATCHING is true a HTTP request will be dispatched
	 * at shutdown that will instantly run the event.
	 *
	 * @since 4.3.0 $unique_for_request arg added
	 *
	 * @param string $hook
	 * @param array  $args
	 * @param bool   $unique_for_request
	 *
	 * @return Event|bool
	 */
	static function schedule_async_event( $hook, $args = [], $unique_for_request = false ) {
		$args_hash = Events::get_event_args_hash( $args );

		if ( $unique_for_request && ! Events::is_event_unique_for_current_request( $hook, $args_hash ) ) {
			return false;
		}

		// Allow trigger based overrides of global instant dispatch setting
		$instant_dispatch = apply_filters( 'automatewoo/events/instant_dispatch', AUTOMATEWOO_ENABLE_INSTANT_EVENT_DISPATCHING, $hook, $args, $unique_for_request );

		if ( $instant_dispatch ) {
			// when using instant event dispatching increase the delay to avoid event
			// duplication since the delay is used by the cron based event runner
			// this acts as a fallback if the http request failed.
			// Note that if the background processor doesn't start because it's already running
			// we automatically reduce this delay
			$delay = 180;
		}
		else {
			$delay = 5;
		}

		// Allow the $unique_for_request logic to work even if the event is not created
		// e.g. when the ::schedule_event() is filtered by a third party.
		Events::add_to_events_created_in_request_store( $hook, $args_hash );

		$date = aw_normalize_date( time() + $delay );

		$event = Events::schedule_event( $date, $hook, $args );

		if ( $event && $instant_dispatch ) {
			Events::dispatch_events_starter_request_at_shutdown( $event->get_id() );
		}

		if ( AUTOMATEWOO_LOG_ASYNC_EVENTS ) {
			Logger::info( 'async-event', $hook. ': ' . print_r( $args, true ) );
		}

		return $event;
	}


	/**
	 * Schedules an event.
	 *
	 * @param DateTime|string|int $date Accepts timestamps
	 * @param string              $hook
	 * @param array               $args
	 *
	 * @return Event|false
	 */
	static function schedule_event( $date, $hook, $args = [] ) {
		$date = aw_normalize_date( $date );

		if ( ! $date ) {
			return false;
		}

		/**
		 * Allow third parties to store events in their own system.
		 *
		 * @since 4.4.0
		 *
		 * @param bool     $short_circuit Return true to prevent the normal event from being created.
		 * @param DateTime $date          The date the event should run.
		 * @param string   $hook          The event hook.
		 * @param array    $args          The event args.
		 */
		if ( apply_filters( 'automatewoo/before_schedule_event', false, $date, $hook, $args ) ) {
			return false;
		}

		$event = new Event();
		$event->set_status( 'pending' );
		$event->set_hook( $hook );
		$event->set_args( $args );
		$event->set_date_scheduled( $date );
		$event->save();
		return $event;
	}


	/**
	 * Unschedules all events attached to the hook with the specified arguments.
	 *
	 * @param string $hook
	 * @param array $args optional
	 */
	static function clear_scheduled_hook( $hook, $args = [] ) {
		/**
		 * Allow third parties to store events in their own system.
		 *
		 * @since 4.4.0
		 *
		 * @param bool   $short_circuit Return true to prevent the normal events from being cleared.
		 * @param string $hook          The event hook.
		 * @param array  $args          The event args.
		 */
		if ( apply_filters( 'automatewoo/before_clear_scheduled_hook', false, $hook, $args ) ) {
			return;
		}

		$query = new Event_Query();
		$query->where('hook', $hook );

		if ( $args ) {
			$query->where('args_hash', Events::get_event_args_hash( $args )  );
		}

		$events = $query->get_results();

		foreach( $events as $event ) {
			$event->delete();
		}
	}


	/**
	 * Experimental method to run the events background processor at the shut down event.
	 * To reduce the delay involved in async events.
	 *
	 * @param int|bool $event_id
	 * @since 3.7
	 */
	static function dispatch_events_starter_request_at_shutdown( $event_id ) {
		self::$events_runner_async_request_data[] = $event_id;
		add_action( 'shutdown', [ 'AutomateWoo\Events', 'dispatch_events_starter_request' ] );
	}


	static function dispatch_events_starter_request() {
		self::get_event_runner_async_request()->data( self::$events_runner_async_request_data )->dispatch();
	}


	/**
	 * Adds an event to the $events_created_in_request store.
	 *
	 * @since 4.3.0
	 *
	 * @param string $hook The event hook.
	 * @param string $args_hash The event args hash.
	 */
	static function add_to_events_created_in_request_store( $hook, $args_hash ) {
		self::$events_created_in_request[] = $hook . '|' . $args_hash;
	}


	/**
	 * @since 4.3.0
	 *
	 * @param string $hook
	 * @param string $args_hash
	 *
	 * @return bool
	 */
	static function is_event_unique_for_current_request( $hook, $args_hash ) {
		return ! in_array( $hook . '|' . $args_hash, self::$events_created_in_request );
	}


	/**
	 * @since 4.3.0
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	static function get_event_args_hash( $args ) {
		return md5( serialize( Clean::recursive( $args ) ) );
	}


}
