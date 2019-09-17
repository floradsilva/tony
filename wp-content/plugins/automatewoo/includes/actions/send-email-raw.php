<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Action_Send_Email_Raw
 * @since 3.6.0
 */
class Action_Send_Email_Raw extends Action_Send_Email_Abstract {

	/**
	 * Get the email type.
	 *
	 * @return string
	 */
	public function get_email_type() {
		return 'html-raw';
	}

	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Send Email - Raw HTML [BETA]', 'automatewoo' );
		$this->description = __( "This action sends emails with only the HTML/CSS entered in the action's HTML field and is designed for advanced use only. This is different from the standard Send Email action, which inserts the email content into a template. Some variables may display unexpectedly due to the different CSS. Please note that you should include an unsubscribe link by using the variable {{ unsubscribe_url }}.", 'automatewoo' );
	}


	function load_fields() {
		parent::load_fields();

		$include_aw_css = new Fields\Checkbox();
		$include_aw_css->set_name( 'include_aw_css' );
		$include_aw_css->set_title( __( 'Include AutomateWoo CSS', 'automatewoo' ) );
		$include_aw_css->set_default_to_checked( true );
		$include_aw_css->set_description( __( 'Checking this box adds the basic AutomateWoo CSS that is used to style variables to your custom HTML.', 'automatewoo' ) );

		$html = new Fields\HTML_Textarea();
		$html->set_name( 'email_html' );
		$html->set_title( __( 'Email HTML', 'automatewoo' ) );
		$html->set_description( __( 'Any CSS included in the HTML will be automatically inlined.', 'automatewoo' ) );
		$html->set_variable_validation();
		$html->set_rows( 14 );
		$html->set_required();

		$this->add_field( $include_aw_css );
		$this->add_field( $html );
	}


	/**
	 * Generates the HTML content for the email
	 * @return string|\WP_Error
	 */
	function preview() {
		$html = $this->get_option('email_html', true, true );
		$subject = $this->get_option( 'subject', true );
		$include_aw_css = $this->get_option('include_aw_css' );

		$current_user = wp_get_current_user();
		wp_set_current_user( 0 ); // no user should be logged in

		$email = $this->get_workflow_email_object();
		$email->set_recipient( $current_user->get('user_email') );
		$email->set_subject( $subject );
		$email->set_content( $html );
		$email->set_include_automatewoo_styles( $include_aw_css );

		return $email->get_email_body();
	}


	/**
	 * @param array $send_to
	 * @return \WP_Error|true
	 */
	function send_test( $send_to = [] ) {
		$html = $this->get_option('email_html', true, true );
		$subject = $this->get_option( 'subject', true );
		$include_aw_css = $this->get_option('include_aw_css' );

		// no user should be logged in
		wp_set_current_user( 0 );

		foreach ( $send_to as $recipient ) {

			$email = $this->get_workflow_email_object();
			$email->set_recipient( $recipient );
			$email->set_subject( $subject );
			$email->set_content( $html );
			$email->set_include_automatewoo_styles( $include_aw_css );

			$sent = $email->send();

			if ( is_wp_error( $sent ) ) {
				return $sent;
			}
		}

		return true;
	}


	function run() {
		$recipients     = $this->get_option( 'to', true );
		$html           = $this->get_option( 'email_html', true, true );
		$subject        = $this->get_option( 'subject', true );
		$include_aw_css = $this->get_option( 'include_aw_css' );

		$recipients = Emails::parse_recipients_string( $recipients );

		foreach ( $recipients as $recipient_email => $recipient_args ) {

			$email = $this->get_workflow_email_object();
			$email->set_recipient( $recipient_email );
			$email->set_subject( $subject );
			$email->set_content( $html );
			$email->set_include_automatewoo_styles( $include_aw_css );

			if ( $recipient_args['notracking'] ) {
				$email->set_tracking_enabled( false );
			}

			$sent = $email->send();

			$this->add_send_email_result_to_workflow_log( $sent );
		}
	}

}
