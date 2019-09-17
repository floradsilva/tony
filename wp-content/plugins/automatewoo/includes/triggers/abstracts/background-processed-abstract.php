<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Class Trigger_Background_Process_Abstract
 *
 * @since 4.5
 * @package AutomateWoo
 */
abstract class Trigger_Background_Processed_Abstract extends Trigger {

	/**
	 * Set that the trigger supports customer time of day functions
	 */
	const SUPPORTS_CUSTOM_TIME_OF_DAY = true;

	/**
	 * Method that the 'workflows' background processor will pass data back to when processing.
	 *
	 * @param \AutomateWoo\Workflow $workflow
	 * @param array                 $data
	 */
	abstract public function handle_background_task( $workflow, $data );

	/**
	 * Should return an array of tasks to be background processed.
	 *
	 * @param \AutomateWoo\Workflow $workflow
	 * @param int                   $limit The limit to use when querying tasks.
	 * @param int                   $offset The offset to use when querying tasks.
	 *
	 * @return array
	 */
	abstract public function get_background_tasks( $workflow, $limit, $offset = 0 );

	/**
	 * Register hooks.
	 */
	public function register_hooks() {
		// This action only needs to be added once for all custom time of day triggers
		if ( ! has_action( 'automatewoo/custom_time_of_day_workflow', [ 'AutomateWoo\Workflow_Background_Process_Helper', 'init_process' ] ) ) {
			add_action( 'automatewoo/custom_time_of_day_workflow', [ 'AutomateWoo\Workflow_Background_Process_Helper', 'init_process' ], 10, 2 );
		}
	}

	/**
	 * Returns the time of day field.
	 *
	 * @return Fields\Time
	 */
	protected function get_field_time_of_day() {
		$time = new Fields\Time();
		$time->set_title( __( 'Time of day', 'automatewoo' ) );
		$time->set_description( __( "Set the time in your site's timezone that the workflow will be triggered. If no time is set the workflow will run at midnight. If you set a time that has already passed for today the workflow will not run until tomorrow. The workflow will never be run twice in the same day. It's not possible to set a time after 23:00 which gives the background processor at least 1 hour to run any tasks.", 'automatewoo' ) );

		// Set the max hours value to 22 to prevent scheduling workflows in the last hour of the day
		// This way we always have at least 1 hour to run the tasks for the current day
		$time->max_hours = 22;

		return $time;
	}


}
