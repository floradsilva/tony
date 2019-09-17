<?php
// phpcs:ignoreFile

namespace AutomateWoo;

abstract class Database_Update {

	/** @var bool  */
	protected $is_complete = false;

	/** @var int  */
	protected $items_processed = 0;

	/** @var string */
	protected $version;

	/**
	 * Should return true if update is complete, false if not complete and needs to be run again
	 * @return bool
	 */
	abstract protected function process();

	/**
	 * Optional method, runs before update starts
	 */
	protected function start() {}

	/**
	 * Optional method, runs after update ends
	 */
	protected function finish() {}


	/**
	 * @return string
	 */
	protected function get_started_option_name() {
		return 'aw_update_started_' . $this->version;
	}


	function dispatch_process() {

		if ( ! get_option( $this->get_started_option_name() ) ) {
			update_option( $this->get_started_option_name(), 1, true );
			$this->start();
			Logger::info( 'updates', "AutomateWoo - Started {$this->version} database update" );
		}

		$complete = $this->process();

		if ( $complete ) {
			$this->is_complete = true;
			$this->finish();
			delete_option( $this->get_started_option_name() );
			Logger::info( 'updates', "AutomateWoo - Finished {$this->version} database update" );
		}
	}


	/**
	 * @return bool
	 */
	function is_complete() {
		return $this->is_complete;
	}


	/**
	 * Returns the count of items run in a single request, not a running total.
	 *
	 * @return int
	 */
	public function get_items_processed_count() {
		return $this->items_processed;
	}


	/**
	 * Return 0 if the total is unknown.
	 *
	 * @since 4.3.0
	 * @return int
	 */
	public function get_items_to_process_count() {
		return 0;
	}


}
