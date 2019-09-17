<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Database_Table_Guest_Meta
 * @since 2.9
 */
class Database_Table_Guest_Meta extends Database_Table {

	function __construct() {
		global $wpdb;

		$this->name = $wpdb->prefix . 'automatewoo_guest_meta';
		$this->primary_key = 'meta_id';
		$this->object_id_column = 'guest_id';
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return [
			'meta_id' => '%d',
			'guest_id' => '%d',
			'meta_key' => '%s',
			'meta_value' => '%s',
		];
	}


	/**
	 * @return string
	 */
	function get_install_query() {
		return "CREATE TABLE {$this->get_name()} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			guest_id bigint(20) NULL,
			meta_key varchar(255) NULL,
			meta_value longtext NOT NULL default '',
			PRIMARY KEY  (meta_id),
			KEY guest_id (guest_id),
			KEY meta_key (meta_key({$this->max_index_length}))
			) {$this->get_collate()};";
	}

}

return new Database_Table_Guest_Meta();
