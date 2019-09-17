<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Abstract class for tools that are processed in the background.
 *
 * @since 3.8
 */
abstract class Tool_Background_Processed_Abstract extends Tool_Abstract {

	/** @var bool */
	public $is_background_processed = true;


	function __construct() {
		$this->additional_description = __( 'If you are processing a large number of items they will be processed in the background.', 'automatewoo' );
	}


	/**
	 * Method to handle individual background tasks.
	 * $task array will not be sanitized.
	 *
	 * @param array $task
	 * @return void
	 */
	abstract public function handle_background_task( $task );

}

