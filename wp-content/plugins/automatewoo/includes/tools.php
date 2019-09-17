<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Tools
 * @since 2.4.5
 */
class Tools {

	/** @var Tool_Abstract[] $tools */
	public static $tools = [];


	/**
	 * @return Tool_Abstract[]
	 */
	static function get_tools() {

		if ( empty( self::$tools ) ) {

			$path = AW()->path( '/includes/tools/' );

			$tool_includes = [];

			$tool_includes[] = Options::optin_enabled() ? $path . 'optin-importer.php' : $path . 'optout-importer.php';
			$tool_includes[] = $path . 'manual-orders-trigger.php';

			if ( Integrations::is_subscriptions_active() ) {
				$tool_includes[] = $path . 'manual-subscriptions-trigger.php';
			}

			$tool_includes[] = $path . 'guest-eraser.php';
			$tool_includes[] = $path . 'reset-workflow-records.php';

			$tool_includes = apply_filters( 'automatewoo/tools', $tool_includes );

			foreach ( $tool_includes as $tool_include ) {
				/** @var Tool_Abstract $class */
				$class = require_once $tool_include;
				self::$tools[$class->get_id()] = $class;
			}
		}

		return self::$tools;
	}


	/**
	 * @param $id
	 * @return Tool_Abstract|false
	 */
	static function get_tool( $id ) {
		$tools = self::get_tools();

		if ( isset( $tools[$id] ) ) {
			return $tools[$id];
		}

		return false;
	}


	/**
	 * @param array $tasks
	 * @return bool|\WP_Error
	 */
	static function init_background_process( $tasks ) {
		/** @var Background_Processes\Tools $process */
		$process = Background_Processes::get('tools');
		$process->data( $tasks )->start();
		return true;
	}

}
