<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Database_Tables
 * @since 2.8.2
 */
class Database_Tables extends Registry {

	/** @var array */
	static $includes;

	/** @var Database_Table[] */
	static $loaded = [];


	/**
	 * Updates any tables as required
	 */
	static function install_tables() {
		global $wpdb;

		$wpdb->hide_errors();

		foreach( self::get_all() as $table ) {
			$table->install();
		}
	}


	/**
	 * @return array
	 */
	static function load_includes() {
		$path = AW()->path( '/includes/database-tables/' );

		$includes = [
			'guests' => $path . 'guests.php',
			'guest-meta' => $path . 'guest-meta.php',
			'carts' => $path . 'carts.php',
			'queue' => $path . 'queue.php',
			'queue-meta' => $path . 'queue-meta.php',
			'logs' => $path . 'logs.php',
			'log-meta' => $path . 'log-meta.php',
			'unsubscribes' => $path . 'unsubscribes.php',
			'customers' => $path . 'customers.php',
			'customer-meta' => $path . 'customer-meta.php',
			'events' => $path . 'events.php',
		];

		return apply_filters( 'automatewoo/database_tables', $includes );
	}


	/**
	 * @return Database_Table[]
	 */
	static function get_all() {
		return parent::get_all();
	}

	/**
	 * Get a database table object.
	 *
	 * @param $table_id
	 *
	 * @return Database_Table
	 *
	 * @throws Exception When table failed to load.
	 */
	static function get( $table_id ) {
		$table = parent::get( $table_id );

		if ( $table instanceof Database_Table ) {
			return $table;
		}

		throw new Exception( sprintf( __( "Failed to load the '%s' database table.", 'automatewoo' ), $table_id ) );
	}


	/**
	 * @deprecated since 3.8
	 * @param $table_id
	 * @return Database_Table
	 */
	static function get_table( $table_id ) {
		return self::get( $table_id );
	}

}
