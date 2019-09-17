<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_MailChimp_Abstract
 */
abstract class Action_MailChimp_Abstract extends Action {

	function load_admin_details() {
		$this->group = __( 'MailChimp', 'automatewoo' );
	}


	/**
	 * @return Fields\Select
	 */
	function add_list_field() {

		$list_select = ( new Fields\Select() )
			->set_title( __( 'List', 'automatewoo' ) )
			->set_name( 'list' )
			->set_options( Integrations::mailchimp()->get_lists() )
			->set_required();

		$this->add_field( $list_select );
		return $list_select;
	}


	/**
	 * Get the MailChimp contact email field.
	 *
	 * @since 4.5
	 *
	 * @return Fields\Text
	 */
	function get_contact_email_field() {
		$field = new Fields\Text();
		$field->set_name( 'email' );
		$field->set_title( __( 'Contact email', 'automatewoo' ) );
		$field->set_description( __( 'Use variables such as {{ customer.email }} here. If blank {{ customer.email }} will be used.', 'automatewoo' ) );
		$field->set_placeholder( '{{ customer.email }}' );
		$field->set_variable_validation();
		return $field;
	}


	/**
	 * Get the contact email option. Defaults to {{ customer.email }}.
	 *
	 * @since 4.5
	 *
	 * @return string|bool
	 */
	function get_contact_email_option() {
		$email = Clean::email( $this->get_option( 'email', true ) );

		if ( $email ) {
			return $email;
		}

		$customer = $this->workflow->data_layer()->get_customer();

		if ( ! $customer ) {
			return false;
		}

		return $customer->get_email();
	}

}
