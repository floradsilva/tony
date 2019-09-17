<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Database_Table_Log_Meta
 * @since 2.8.2
 */
class Database_Table_Log_Meta extends Database_Table {

	function __construct() {
		global $wpdb;

		$this->name = $wpdb->prefix . 'automatewoo_log_meta';
		$this->primary_key = 'meta_id';
		$this->object_id_column = 'log_id';
	}


	/**
	 * @return array
	 */
	function get_columns() {
		return [
			'meta_id' => '%d',
			'log_id' => '%d',
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
			log_id bigint(20) NULL,
			meta_key varchar(255) NULL,
			meta_value longtext NOT NULL default '',
			PRIMARY KEY  (meta_id),
			KEY log_id (log_id),
			KEY meta_key (meta_key({$this->max_index_length}))
			) {$this->get_collate()};";
	}

}

return new Database_Table_Log_Meta();
