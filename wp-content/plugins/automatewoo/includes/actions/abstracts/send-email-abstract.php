<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Abstract class for send email actions.
 *
 * @class Action_Send_Email_Abstract
 * @since 4.4.0
 */
abstract class Action_Send_Email_Abstract extends Action {

	/**
	 * Get the email type (html-template, html-raw, plain-text).
	 *
	 * @return string
	 */
	abstract public function get_email_type();

	/**
	 * Get email preview.
	 *
	 * @return string|\WP_Error
	 */
	abstract public function preview();

	/**
	 * Load admin props.
	 */
	public function load_admin_details() {
		$this->group = __( 'Email', 'automatewoo' );
	}

	/**
	 * Load fields.
	 */
	public function load_fields() {
		$to = new Fields\Text();
		$to->set_name( 'to' );
		$to->set_title( __( 'To', 'automatewoo' ) );
		$to->set_description( __( 'Enter emails here or use variables such as {{ customer.email }}. Multiple emails can be separated by commas. Add <b>--notracking</b> after an email to disable open and click tracking for that recipient.', 'automatewoo' ) );
		$to->set_placeholder( __( 'E.g. {{ customer.email }}, admin@example.org --notracking', 'automatewoo' ) );
		$to->set_variable_validation();
		$to->set_required();

		$subject = new Fields\Text();
		$subject->set_name( 'subject' );
		$subject->set_title( __( 'Email subject', 'automatewoo' ) );
		$subject->set_variable_validation();
		$subject->set_required();

		$this->add_field( $to );
		$this->add_field( $subject );
	}

	/**
	 * Get workflow email object for this action.
	 *
	 * @return Workflow_Email
	 */
	public function get_workflow_email_object() {
		$email = new Workflow_Email( $this->workflow );
		$email->set_type( $this->get_email_type() );
		return $email;
	}


	/**
	 * Log the result of a send email attempt.
	 *
	 * @param \WP_Error|bool $result
	 */
	public function add_send_email_result_to_workflow_log( $result ) {
		if ( is_wp_error( $result ) ) {
			$this->workflow->log_action_email_error( $result, $this );
		} else {
			$this->workflow->log_action_note( $this, __( 'Email successfully sent.', 'automatewoo' ) );
		}
	}

}
