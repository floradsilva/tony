<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Queue_Manager
 */
class Queue_Manager {

	/**
	 * @return int
	 */
	static function get_batch_size() {
		return (int) apply_filters( 'automatewoo_queue_batch_size', 50 );
	}


	/**
	 * @return int
	 */
	static function get_delete_failed_after() {
		return (int) apply_filters( 'automatewoo_failed_events_delete_after', 30 );
	}


	/**
	 * @param $code
	 * @return string
	 */
	static function get_failure_message( $code ) {

		$messages = [
			Queued_Event::F_WORKFLOW_INACTIVE => __( 'The workflow was deleted or deactivated.', 'automatewoo' ),
			Queued_Event::F_MISSING_DATA => __( 'Some of the required data was not found. For example, the order could have been deleted.', 'automatewoo' ),
			Queued_Event::F_FATAL_ERROR => __( 'A fatal error occurred while running the queued event.', 'automatewoo' ),
		];

		if ( isset( $messages[$code] ) ) {
			return $messages[$code];
		}

		return __( 'Cause of queued event failure is unknown.', 'automatewoo' );
	}


	/**
	 * Check for queued workflow runs
	 */
	static function check_for_queued_events() {

		/** @var Background_Processes\Queue $process */
		$process = Background_Processes::get('queue');

		// don't start a new process until the previous is finished
		if ( $process->has_queued_items() ) {
			$process->maybe_schedule_health_check();
			return;
		}

		$query = ( new Queue_Query() )
			->set_limit( self::get_batch_size() )
			->set_ordering( 'date', 'ASC' )
			->where_date_due( new DateTime(), '<' )
			->where_failed( false )
			->set_return( 'ids' );

		if ( ! $events = $query->get_results() ) {
			return;
		}

		$process->data( $events )->start();
	}


	/**
	 * Delete old for queued events that failed
	 */
	static function check_for_failed_queued_events() {

		$clear_date = new DateTime();
		$clear_date->modify( '-' . self::get_delete_failed_after() . ' days');

		$query = ( new Queue_Query() )
			->set_limit( 15 )
			->set_ordering('date', 'ASC')
			->where_date_due( $clear_date, '<' )
			->where_failed( true );

		foreach ( $query->get_results() as $result ) {
			$result->delete();
		}
	}


	/**
	 * Returns the meta key that a data item is mapped to in queue meta.
	 *
	 * @param $data_type_id string
	 * @return bool|string
	 */
	static function get_data_layer_storage_key( $data_type_id ) {
		return 'data_item_' . $data_type_id;
	}


	/**
	 * @param $data_type_id
	 * @param $data_item : must be validated
	 * @return mixed
	 */
	static function get_data_layer_storage_value( $data_type_id, $data_item ) {
		// same method as logs
		return Logs::get_data_layer_storage_value( $data_type_id, $data_item );
	}

}