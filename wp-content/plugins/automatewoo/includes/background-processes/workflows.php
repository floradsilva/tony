<?php
// phpcs:ignoreFile

namespace AutomateWoo\Background_Processes;

use AutomateWoo\Trigger_Background_Processed_Abstract;
use AutomateWoo\Clean;
use AutomateWoo\Workflow_Factory;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Background processor for any workflows.
 *
 * It's better not to have a separate process for each workflow since there doesn't
 * appear to be any restriction on different processes running at the same time.
 *
 * Triggers that use this background process must extend Trigger_Background_Processed_Abstract.
 *
 * @since 3.7
 */
class Workflows extends Base {

	/** @var string  */
	public $action = 'workflows';


	/**
	 * @param array $data
	 * @return mixed
	 */
	protected function task( $data ) {
		$workflow = isset( $data['workflow_id'] ) ? Workflow_Factory::get( $data['workflow_id'] ) : false;
		$workflow_data = isset( $data['workflow_data'] ) ? Clean::recursive( $data['workflow_data'] ) : [];

		if ( ! $workflow ) {
			return false;
		}

		if ( ! $trigger = $workflow->get_trigger() ) {
			return false;
		}

		if ( $trigger instanceof Trigger_Background_Processed_Abstract ) {
			$trigger->handle_background_task( $workflow, $workflow_data );
		}

		return false;
	}

}

return new Workflows();
