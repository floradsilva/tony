<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * Cron manager
 * @class Cron
 */
class Cron {

	/** @var array : worker => schedule */
	static $workers = [
		'events' => 'automatewoo_one_minute',
		'two_minute' => 'automatewoo_two_minutes',
		'five_minute' => 'automatewoo_five_minutes',
		'fifteen_minute' => 'automatewoo_fifteen_minutes',
		'thirty_minute' => 'automatewoo_thirty_minutes',
		'hourly' => 'hourly',
		'four_hourly' => 'automatewoo_four_hours',
		'daily' => 'daily',
		'two_days' => 'automatewoo_two_days',
		'weekly' => 'automatewoo_weekly'
	];


	/**
	 * Init cron
	 */
	static function init() {

		add_filter( 'cron_schedules', [ __CLASS__, 'add_schedules' ], 100 );

		foreach ( self::$workers as $worker => $schedule ) {
			add_action( 'automatewoo_' . $worker . '_worker', [ __CLASS__, 'before_worker' ], 1 );
		}

		add_action( 'admin_init', [ __CLASS__, 'add_events' ] );

		add_action( 'automatewoo_five_minute_worker', [ __CLASS__, 'check_for_gmt_offset_change' ] );
		add_action( 'automatewoo_thirty_minute_worker', [ __CLASS__, 'check_midnight_cron' ], 1 );
		add_action( 'automatewoo_midnight', [ __CLASS__, 'update_midnight_cron_last_run' ], 1 );

		// set up midnight cron job, but doesn't repair it (which is important)
		add_action( 'admin_init', [ __CLASS__, 'setup_midnight_cron' ] );
	}


	/**
	 * Prevents workers from working if they have done so in the past 30 seconds
	 */
	static function before_worker() {

		$action = current_action();

		if ( self::is_worker_locked( $action ) ) {
			remove_all_actions( $action ); // prevent actions from running
			return;
		}

		@set_time_limit(300);

		self::update_last_run( $action );
	}


	/**
	 * @param $action
	 * @return \DateTime|bool
	 */
	static function get_last_run( $action ) {
		$last_runs = get_option('aw_workers_last_run');
		if ( is_array( $last_runs ) && isset( $last_runs[$action] ) ) {
			$date = new DateTime();
			$date->setTimestamp( $last_runs[$action] );
			return $date;
		}
		else {
			return false;
		}
	}


	/**
	 * @param $action
	 */
	static function update_last_run( $action ) {
		$last_runs = get_option('aw_workers_last_run');

		if ( ! $last_runs ) $last_runs = [];

		$last_runs[$action] = time();

		update_option( 'aw_workers_last_run', $last_runs, false );
	}


	/**
	 * @param $action
	 * @return int|false
	 */
	static function get_worker_interval( $action ) {
		$schedules = wp_get_schedules();
		$schedule = wp_get_schedule( $action );

		if ( isset( $schedules[$schedule] ) ) {
			return $schedules[$schedule]['interval'];
		}

		return false;
	}


	/**
	 * Checks if worker started running less than 30 seconds
	 *
	 * @param $action
	 * @return bool
	 */
	static function is_worker_locked( $action ) {
		if ( ! $time_last_run = self::get_last_run( $action ) ) {
			return false;
		}

		$time_unlocked = clone $time_last_run;
		$time_unlocked->modify( '+30 seconds' );

		if ( $time_unlocked->getTimestamp() > time() ) {
			return true;
		}

		return false;
	}


	/**
	 * Add cron workers
	 */
	static function add_events() {
		foreach ( self::$workers as $worker => $schedule ) {
			$hook = 'automatewoo_' . $worker . '_worker';

			if ( ! wp_next_scheduled( $hook ) ) {
				wp_schedule_event( time(), $schedule, $hook );
			}
		}
	}


	/**
	 * @param $schedules
	 * @return mixed
	 */
	static function add_schedules( $schedules ) {

		$schedules['automatewoo_one_minute'] = [
			'interval' => 60,
			'display' => __( 'One minute', 'automatewoo' )
		];

		$schedules['automatewoo_two_minutes'] = [
			'interval' => 120,
			'display' => __( 'Two minutes', 'automatewoo' )
		];

		$schedules['automatewoo_five_minutes'] = [
			'interval' => 300,
			'display' => __( 'Five minutes', 'automatewoo' )
		];

		$schedules['automatewoo_fifteen_minutes'] = [
			'interval' => 900,
			'display' => __( 'Fifteen minutes', 'automatewoo' )
		];

		$schedules['automatewoo_thirty_minutes'] = [
			'interval' => 1800,
			'display' => __( 'Thirty minutes', 'automatewoo' )
		];

		$schedules['automatewoo_two_days'] = [
			'interval' => 172800,
			'display' => __( 'Two days', 'automatewoo' )
		];

		$schedules['automatewoo_four_hours'] = [
			'interval' => 14400,
			'display' => __( 'Four hours', 'automatewoo' )
		];

		$schedules['automatewoo_weekly'] = [
			'interval' => 604800,
			'display' => __('Once weekly', 'automatewoo' )
		];

		return $schedules;
	}


	/**
	 * Track changes in the GMT offset such as DST
	 *
	 * @since 3.8
	 */
	static function check_for_gmt_offset_change() {
		$new_offset = Time_Helper::get_timezone_offset();
		$existing_offset = get_option( 'automatewoo_gmt_offset' );

		if ( $existing_offset === false ) {
			update_option( 'automatewoo_gmt_offset', $new_offset, false );
			return;
		}

		if ( $existing_offset != $new_offset ) {
			do_action( 'automatewoo/gmt_offset_changed', $new_offset, $existing_offset );
			update_option( 'automatewoo_gmt_offset', $new_offset, false );
		}
	}

	/**
	 * Set midnight cron, if not already set
	 * @since 3.8
	 */
	static function setup_midnight_cron() {
		if ( $next = wp_next_scheduled( 'automatewoo_midnight' ) ) {
			return false; // already setup
		}

		// calculate next midnight in the site's timezone
		$date = new DateTime( 'now' );
		$date->convert_to_site_time();
		$date->set_time_to_day_start();
		// actually trigger now instead of tomorrow, to avoid issues with custom time of day triggers
		// these triggers could skip 1 day if we don't run the midnight cron immediately when adding
		// TODO remove in the future
		//$date->modify('+1 day');
		$date->convert_to_utc_time(); // convert back to UTC

		self::update_midnight_cron( $date );
	}


	/**
	 * @return bool
	 */
	static function is_midnight_cron_correct() {
		if ( ! $next = wp_next_scheduled( 'automatewoo_midnight' ) ) {
			return false;
		}
		$date = new DateTime();
		$date->setTimestamp( $next );
		$date->convert_to_site_time();
		return $date->format('Hi') == '0000';
	}


	/**
	 * Sets a new time for the midnight cron.
	 *
	 * @param DateTime $date GMT
	 */
	static function update_midnight_cron( $date ) {
		wp_clear_scheduled_hook( 'automatewoo_midnight' );
		wp_schedule_event( $date->getTimestamp(), 'daily', 'automatewoo_midnight' );
	}

	/**
	 * Check the midnight cron job is correctly scheduled.
	 *
	 * If schedule is not correct this method fixes the schedule.
	 *
	 * @since 4.6.0
	 */
	public static function check_midnight_cron() {
		if ( self::is_midnight_cron_correct() ) {
			return;
		}

		// Repair the cron job schedule
		$date = new DateTime();
		$date->convert_to_site_time();
		$date->set_time_to_day_start();

		// If midnight cron should not run for today, schedule it for tomorrow
		// Otherwise, run it now because it's better to run at a slightly wrong time rather than not run at all.
		if ( ! self::should_midnight_cron_run_today() ) {
			// Replace date with last run +1 day, i.e. the day after last run
			$date = self::get_midnight_cron_last_run();
			$date->modify('+1 day');
		}

		$date->convert_to_utc_time();

		self::update_midnight_cron( $date );
	}

	/**
	 * Update the last run date of the midnight cron to now.
	 *
	 * Store last run in site time as Y-m-d.
	 *
	 * This is stored as site time because the goal of the midnight cron event is to run once per day
	 * in the site's timezone. Storing in site time means we can handle DST timezone changes better.
	 *
	 * @since 4.6.0
	 */
	public static function update_midnight_cron_last_run() {
		$now = new DateTime();
		$now->convert_to_site_time();
		update_option( 'automatewoo_midnight_cron_last_run', $now->format( 'Y-m-d' ), false );
	}

	/**
	 * Get the last run date of the midnight cron in site time.
	 *
	 * @since 4.6.0
	 *
	 * @return DateTime|false
	 */
	public static function get_midnight_cron_last_run() {
		$last_run = get_option( 'automatewoo_midnight_cron_last_run' );
		return $last_run ? aw_normalize_date( $last_run ) : false;
	}

	/**
	 * Did the midnight cron task run today (in local time)?
	 *
	 * Also returns true if midnight cron has run for tomorrow. E.g. in the case of DST changes.
	 *
	 * @since 4.6.0
	 *
	 * @return bool
	 */
	public static function should_midnight_cron_run_today() {
		$last_run = self::get_midnight_cron_last_run();

		if ( ! $last_run ) {
			return true;
		}

		$last_run->set_time_to_day_end();

		$now = new DateTime();
		$now->convert_to_site_time();

		// Return false if cron has run today or even for tomorrow
		return $now > $last_run;
	}

}
