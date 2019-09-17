<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Workflow management class
 *
 * @class Workflows
 */
class Workflows {


	/**
	 * Add hooks
	 */
	static function init() {
		$self = 'AutomateWoo\Workflows'; /** @var $self Workflows (for IDE) */

		add_action( 'save_post', [ $self, 'do_workflow_updated_action' ], 20 );
		add_action( 'delete_post', [ $self, 'maybe_cleanup_workflow_data' ] );

		// update cron events after workflow is updated
		add_action( 'automatewoo/workflow/updated', [ $self, 'maybe_schedule_custom_time_of_day_event' ] );

		// schedule events at the start of each day
		add_action( 'automatewoo_midnight', [ $self, 'schedule_all_custom_time_of_day_events' ] );

		// reschedule after gmt offset change
		add_action( 'automatewoo/gmt_offset_changed', [ $self, 'schedule_all_custom_time_of_day_events' ], 20 );
	}


	/**
	 * @param $post_id
	 */
	static function do_workflow_updated_action( $post_id ) {
		if ( get_post_type( $post_id ) === 'aw_workflow' && get_post_status( $post_id ) !== 'auto-draft' ) {
			do_action('automatewoo/workflow/updated', (int) $post_id );
		}
	}


	/**
	 * @param int $post_id
	 */
	static function maybe_cleanup_workflow_data( $post_id ) {
		if ( get_post_type( $post_id ) === 'aw_workflow' ) {
			self::delete_related_data( $post_id );
		}
	}


	/**
	 * Delete logs, unsubscribes, queue related to a workflow
	 *
	 * @param int $workflow_id
	 */
	static function delete_related_data( $workflow_id ) {
		$logs_query = ( new Log_Query() )->where_workflow( $workflow_id );
		$queue_query = ( new Queue_Query() )->where_workflow( $workflow_id );

		$data = array_merge( $logs_query->get_results(), $queue_query->get_results() );

		foreach ( $data as $item ) {
			/** @var Model $item */
			$item->delete();
		}
	}


	/**
	 * Updates custom time of day cron hook if needed.
	 *
	 * @since 3.8
	 * @param int $workflow_id
	 */
	static function maybe_schedule_custom_time_of_day_event( $workflow_id ) {
		$workflow = Workflow_Factory::get( $workflow_id );

		if ( ! $workflow || ! $workflow->is_active() ) {
			return;
		}

		$trigger = $workflow->get_trigger();

		if ( ! $trigger || ! $trigger::SUPPORTS_CUSTOM_TIME_OF_DAY ) {
			return;
		}

		// update the cron event but delete the event if the time was set in the past
		// midnight cron worker will add the events again tomorrow
		self::schedule_custom_time_of_day_event( $workflow, true );
	}


	/**
	 * @since 3.8
	 *
	 * @param Workflow $workflow
	 * @param bool $clear_if_time_has_passed_for_today
	 */
	static function schedule_custom_time_of_day_event( $workflow, $clear_if_time_has_passed_for_today = false ) {
		$hook = 'automatewoo/custom_time_of_day_workflow';

		$time = array_map( 'absint', (array) $workflow->get_trigger_option('time') );

		// calculate time of day in site's timezone
		$datetime = new DateTime( 'now' );
		$datetime->convert_to_site_time();
		$datetime->setTime( isset($time[0]) ? $time[0] : 0, isset($time[1]) ? $time[1] : 0, 0 );
		$datetime->convert_to_utc_time();

		$passed_for_today = $datetime->getTimestamp() < time();

		Events::clear_scheduled_hook( $hook, [ $workflow->get_id() ] );

		if ( $passed_for_today && $clear_if_time_has_passed_for_today ) {
			return;
		}

		Events::schedule_event( $datetime, $hook, [ $workflow->get_id() ] );
	}


	/**
	 * Resets all cron events for custom time of day workflows.
	 *
	 * @since 3.8
	 */
	static function schedule_all_custom_time_of_day_events() {
		$query = new Workflow_Query();
		$query->set_trigger( Triggers::get_custom_time_of_day_triggers() );
		$workflows = $query->get_results();
		foreach( $workflows as $workflow ) {
			self::schedule_custom_time_of_day_event( $workflow );
		}
	}


}
