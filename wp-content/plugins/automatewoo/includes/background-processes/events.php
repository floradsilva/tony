<?php
// phpcs:ignoreFile

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Event_Factory;
use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Background processor for events
 */
class Event_Runner extends Base {

	/** @var string  */
	public $action = 'events';


	/**
	 * @param int $event_id
	 * @return bool
	 */
	protected function task( $event_id ) {
		if ( $event = Event_Factory::get( Clean::id( $event_id ) ) ) {
			$event->run();
		}

		return false;
	}

}

return new Event_Runner();
