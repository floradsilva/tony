<?php
// phpcs:ignoreFile

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/**
 * @class Action_Send_Email
 */
class Action_Send_Email extends Action_Send_Email_Abstract {

	/**
	 * Get the email type.
	 *
	 * @return string
	 */
	public function get_email_type() {
		return 'html-template';
	}


	function load_admin_details() {
		parent::load_admin_details();

		$this->title       = __( 'Send Email', 'automatewoo' );
		$this->description = sprintf(
			__( "This action sends an HTML email using a template. The default template matches the style of your WooCommerce transactional emails. <%s>View email templates documentation<%s>.", 'automatewoo' ),
			'a href="' . Admin::get_docs_link( 'email/templates', 'action-description' ) . '" target="_blank"',
			'/a'
		);
	}


	function load_fields() {
		parent::load_fields();

		$heading = ( new Fields\Text() )
			->set_name( 'email_heading' )
			->set_title( __('Email heading', 'automatewoo' ) )
			->set_variable_validation()
			->set_description( __( 'The appearance will depend on your email template. Not all templates support this field.', 'automatewoo' ) );

		$preheader = ( new Fields\Text() )
			->set_name( 'preheader' )
			->set_title( __('Email preheader', 'automatewoo' ) )
			->set_variable_validation()
			->set_description( __( 'A preheader is a short text summary that follows the subject line when an email is viewed in the inbox. If no preheader is set the first text found in the email is used.', 'automatewoo' ) );

		$template = ( new Fields\Select( false ) )
			->set_name('template')
			->set_title( __( 'Template', 'automatewoo' ) )
			->set_description( __( 'Select which template to use when formatting the email. If you select \'None\', the email will have no template but the email will still be sent as an HTML email.', 'automatewoo' ) )
			->set_options( Emails::get_email_templates() );

		$email_content = ( new Fields\Email_Content() ); // no easy way to define data attributes

		$this->add_field( $heading );
		$this->add_field( $preheader );
		$this->add_field( $template );
		$this->add_field( $email_content );
	}


	/**
	 * Generates the HTML content for the email
	 * @return string|\WP_Error
	 */
	function preview() {
		$current_user = wp_get_current_user();

		// no user should be logged in
		wp_set_current_user( 0 );

		$email = $this->get_workflow_email_object();
		$email->set_recipient( $current_user->get('user_email') );
		$email->set_subject( $this->get_option( 'subject', true ) );
		$email->set_heading( $this->get_option('email_heading', true ) );
		$email->set_preheader( trim( $this->get_option( 'preheader', true ) ) );
		$email->set_template( $this->get_option( 'template' ) );
		$email->set_content( $this->get_option('email_content', true, true ) );

		return $email->get_email_body();
	}


	/**
	 * Generates the HTML content for the email
	 * @param array $send_to
	 * @return string|\WP_Error|true
	 */
	function send_test( $send_to = [] ) {
		$email_heading = $this->get_option( 'email_heading', true );
		$email_content = $this->get_option( 'email_content', true, true );
		$subject       = $this->get_option( 'subject', true );
		$preheader     = trim( $this->get_option( 'preheader', true ) );
		$template      = $this->get_option( 'template' );

		wp_set_current_user( 0 ); // no user should be logged in

		foreach ( $send_to as $recipient ) {

			$email = $this->get_workflow_email_object();
			$email->set_recipient( $recipient );
			$email->set_subject( $subject );
			$email->set_heading( $email_heading );
			$email->set_preheader( $preheader );
			$email->set_template( $template );
			$email->set_content( $email_content );

			$sent = $email->send();

			if ( is_wp_error( $sent ) ) {
				return $sent;
			}
		}

		return true;
	}


	function run() {
		$recipients    = $this->get_option( 'to', true );
		$email_heading = $this->get_option( 'email_heading', true );
		$email_content = $this->get_option( 'email_content', true, true );
		$subject       = $this->get_option( 'subject', true );
		$preheader     = $this->get_option( 'preheader', true );
		$template      = $this->get_option( 'template' );

		$recipients = Emails::parse_recipients_string( $recipients );

		foreach ( $recipients as $recipient_email => $recipient_args ) {

			$email = $this->get_workflow_email_object();
			$email->set_recipient( $recipient_email );
			$email->set_subject( $subject );
			$email->set_heading( $email_heading );
			$email->set_preheader( $preheader );
			$email->set_template( $template );
			$email->set_content( $email_content );

			if ( $recipient_args['notracking'] ) {
				$email->set_tracking_enabled( false );
			}

			$sent = $email->send();

			$this->add_send_email_result_to_workflow_log( $sent );
		}
	}


}
