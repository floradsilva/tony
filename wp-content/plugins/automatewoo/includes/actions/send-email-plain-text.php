<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * Action_Send_Email_Plain_Text class.
 *
 * @since 4.4.0
 */
class Action_Send_Email_Plain_Text extends Action_Send_Email_Abstract {

	/**
	 * Get the email type.
	 *
	 * @return string
	 */
	public function get_email_type() {
		return 'plain-text';
	}

	/**
	 * Load admin props.
	 */
	public function load_admin_details() {
		parent::load_admin_details();
		$this->title       = __( 'Send Email - Plain Text', 'automatewoo' );
		$this->description = __( 'This action sends a plain text email. It will contain no HTML which means open tracking and click tracking will not work. Some variables may display unexpectedly due to having HTML removed. If necessary, an unsubscribe link will be added after the email content.', 'automatewoo' );
	}

	/**
	 * Load action fields.
	 */
	public function load_fields() {
		parent::load_fields();

		$text = new Fields\Text_Area();
		$text->set_name( 'email_content' );
		$text->set_title( __( 'Email content', 'automatewoo' ) );
		$text->set_description( __( 'All HTML will be removed from this field when sending. Variables that use HTML may display unexpectedly because of this.', 'automatewoo' ) );
		$text->set_variable_validation();
		$text->set_rows( 14 );

		$this->add_field( $text );
	}

	/**
	 * Generates the HTML content for the email.
	 *
	 * @return string|\WP_Error
	 */
	public function preview() {
		$content = $this->get_option( 'email_content', true );
		$subject = $this->get_option( 'subject', true );

		// no user should be logged in
		$current_user = wp_get_current_user();
		wp_set_current_user( 0 );

		$email = $this->get_workflow_email_object();
		$email->set_recipient( $current_user->get( 'user_email' ) );
		$email->set_subject( $subject );
		$email->set_content( $content );

		$email_body = $email->get_email_body();

		// convert new lines to HTML breaks for preview only
		return nl2br( $email_body, false );
	}

	/**
	 * Send test email.
	 *
	 * @param array $send_to
	 *
	 * @return \WP_Error|true
	 */
	public function send_test( $send_to = [] ) {
		$content = $this->get_option( 'email_content', true );
		$subject = $this->get_option( 'subject', true );

		// no user should be logged in
		wp_set_current_user( 0 );

		foreach ( $send_to as $recipient ) {

			$email = $this->get_workflow_email_object();
			$email->set_recipient( $recipient );
			$email->set_subject( $subject );
			$email->set_content( $content );

			$sent = $email->send();

			if ( is_wp_error( $sent ) ) {
				return $sent;
			}
		}

		return true;
	}

	/**
	 * Run the action.
	 */
	public function run() {
		$content    = $this->get_option( 'email_content', true );
		$subject    = $this->get_option( 'subject', true );
		$recipients = $this->get_option( 'to', true );
		$recipients = Emails::parse_recipients_string( $recipients );

		foreach ( $recipients as $recipient_email => $recipient_args ) {
			$sent = $this->send_email( $recipient_email, $content, $subject, $recipient_args );
			$this->add_send_email_result_to_workflow_log( $sent );
		}
	}

	/**
	 * Send an email to a single recipient.
	 *
	 * @param string $recipient_email
	 * @param string $content
	 * @param string $subject
	 * @param array  $recipient_args
	 *
	 * @return bool|\WP_Error
	 */
	public function send_email( $recipient_email, $content, $subject, $recipient_args = [] ) {
		$email = $this->get_workflow_email_object();
		$email->set_recipient( $recipient_email );
		$email->set_subject( $subject );
		$email->set_content( $content );

		if ( ! empty( $recipient_args['notracking'] ) ) {
			$email->set_tracking_enabled( false );
		}

		return $email->send();
	}

}
