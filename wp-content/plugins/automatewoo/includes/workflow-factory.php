<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @since 3.9
 */
class Workflow_Factory {

	/**
	 * @param int $id
	 * @return Workflow|false
	 */
	static function get( $id ) {
		$id = Clean::id( $id );

		if ( ! $id ) {
			return false;
		}

		$workflow = new Workflow( $id );

		if ( ! $workflow->exists ) {
			return false;
		}

		return $workflow;
	}

}
