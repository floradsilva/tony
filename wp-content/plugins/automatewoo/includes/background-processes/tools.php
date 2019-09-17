<?php
// phpcs:ignoreFile

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Clean;
use AutomateWoo\Tool_Background_Processed_Abstract;
use AutomateWoo\Tools as ToolManager;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Background processor for tools.
 *
 * @since 3.8
 */
class Tools extends Base {

	/** @var string  */
	public $action = 'tools';


	/**
	 * @param array $data
	 * @return mixed
	 */
	protected function task( $data ) {
		$tool = isset( $data['tool_id'] ) ? ToolManager::get_tool( Clean::string( $data['tool_id'] ) ) : false;

		if ( $tool ) {
			/** @var Tool_Background_Processed_Abstract $tool */
			$tool->handle_background_task( $data );
		}

		return false;
	}

}

return new Tools();
