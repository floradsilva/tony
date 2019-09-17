<?php
// phpcs:ignoreFile

/**
 * @class AutomateWoo_Legacy
 */
abstract class AutomateWoo_Legacy {

	/**
	 * @deprecated
	 * @var AutomateWoo\Admin
	 */
	public $admin;

	/** @var AutomateWoo\Database_Tables */
	protected $database_tables;

	/**
	 * @var AutomateWoo\Session_Tracker
	 * @deprecated
	 */
	public $session_tracker;


	/**
	 * @deprecated since 3.6
	 *
	 * @param $id
	 * @return AutomateWoo\Unsubscribe|bool
	 */
	function get_unsubscribe( $id ) {
		return AutomateWoo\Unsubscribe_Factory::get( $id );
	}


	/**
	 * @deprecated since 3.8
	 * @return AutomateWoo\Database_Tables
	 */
	function database_tables() {
		if ( ! isset( $this->database_tables ) ) {
			$this->database_tables = new AutomateWoo\Database_Tables();
		}
		return $this->database_tables;
	}


	/**
	 * @return string
	 * @since 2.4.4
	 * @deprecated use WC_Geolocation::get_ip_address()
	 */
	function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) )
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) )
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		else
			$ip = $_SERVER['REMOTE_ADDR'];
		return $ip;
	}


	/**
	 * @deprecated
	 * @param $id
	 * @return AutomateWoo\Log|bool
	 */
	function get_log( $id ) {
		return AutomateWoo\Log_Factory::get( $id );
	}


	/**
	 * @deprecated
	 * @param $id
	 * @return AutomateWoo\Workflow|bool
	 */
	function get_workflow( $id ) {
		return AutomateWoo\Workflow_Factory::get( $id );
	}


	/**
	 * @deprecated
	 * @param $id
	 * @return AutomateWoo\Queued_Event|bool
	 */
	function get_queued_event( $id ) {
		return AutomateWoo\Queued_Event_Factory::get( $id );
	}


	/**
	 * @deprecated
	 * @param $id
	 * @return AutomateWoo\Guest|bool
	 */
	function get_guest( $id ) {
		return AutomateWoo\Guest_Factory::get( $id );
	}


	/**
	 * @deprecated
	 * @param $id
	 * @return AutomateWoo\Cart|bool
	 */
	function get_cart( $id ) {
		return AutomateWoo\Cart_Factory::get( $id );
	}


}
