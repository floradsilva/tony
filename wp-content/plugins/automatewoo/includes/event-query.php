<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Event_Query
 */
class Event_Query extends Query_Abstract {

	/** @var string */
	public $table_id = 'events';

	/** @var string */
	public $model = 'AutomateWoo\Event';

	/**
	 * @return Event[]
	 */
	function get_results() {
		return parent::get_results();
	}

}
