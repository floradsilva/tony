<?php
// phpcs:ignoreFile

/**
 * @class AW_Reports_Tab_Email_Tracking
 */

if ( ! defined( 'ABSPATH' ) ) exit;


class AW_Reports_Tab_Email_Tracking extends AW_Admin_Reports_Tab_Abstract {

	function __construct() {
		$this->id = 'email-tracking';
		$this->name = __( 'Email & SMS Tracking', 'automatewoo' );
	}


	/**
	 * @return object
	 */
	function get_report_class() {
		require_once AW()->admin_path( '/reports/abstract-graph.php' );
		require_once AW()->admin_path( '/reports/email-tracking.php' );

		return new AutomateWoo\Report_Email_Tracking();
	}
}

return new AW_Reports_Tab_Email_Tracking();
