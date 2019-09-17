<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_MailChimp_Unsubscribe
 */
class Action_MailChimp_Unsubscribe extends Action_MailChimp_Abstract {

	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Remove Contact From List', 'automatewoo' );
	}


	function load_fields() {
		$unsubscribe_only = new Fields\Checkbox();
		$unsubscribe_only->set_name('unsubscribe_only');
		$unsubscribe_only->set_title( __( 'Unsubscribe only', 'automatewoo' ) );
		$unsubscribe_only->set_description( __( 'If checked the user will be unsubscribed instead of deleted.', 'automatewoo' ) );

		$this->add_list_field();
		$this->add_field( $this->get_contact_email_field() );
		$this->add_field( $unsubscribe_only );
	}


	function run() {
		$list_id = $this->get_option( 'list' );
		$email = $this->get_contact_email_option();

		if ( ! $list_id ) {
			return;
		}

		$subscriber = md5( $email );

		if ( $this->get_option('unsubscribe_only') ) {
			Integrations::mailchimp()->request( 'PATCH', "/lists/$list_id/members/$subscriber", [
				'status' => 'unsubscribed',
			]);
		}
		else {
			Integrations::mailchimp()->request( 'DELETE', "/lists/$list_id/members/$subscriber" );
		}
	}

}
