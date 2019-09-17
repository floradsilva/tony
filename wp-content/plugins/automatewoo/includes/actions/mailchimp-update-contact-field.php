<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_MailChimp_Update_Contact_Field
 * @since 2.9
 */
class Action_MailChimp_Update_Contact_Field extends Action_MailChimp_Abstract {

	function load_admin_details() {
		parent::load_admin_details();
		$this->title = __( 'Update List Contact Field', 'automatewoo' );
		$this->description = __( 'The contact must have been added to the list before updating any fields.', 'automatewoo' );
	}


	function load_fields() {

		$field = ( new Fields\Select() )
			->set_name( 'field' )
			->set_title( __( 'Field', 'automatewoo' ) )
			->set_required()
			->set_dynamic_options_reference( 'list' );

		$value = ( new Fields\Text() )
			->set_name( 'value' )
			->set_title( __( 'Field Value', 'automatewoo' ) )
			->set_variable_validation();

		$this->add_list_field();
		$this->add_field( $this->get_contact_email_field() );
		$this->add_field( $field );
		$this->add_field( $value );

	}


	/**
	 * @param $field_name
	 * @param $reference_field_value
	 * @return array
	 */
	function get_dynamic_field_options( $field_name, $reference_field_value = false ) {

		$options = [];
		/** @var Fields\Select $field */
		$field = $this->get_field( $field_name );

		if ( $field && $field_name !== 'field' ) {
			return [];
		}

		// if reference value is not set load the last saved value, used when initially loading an action page
		if ( ! $reference_field_value ) {
			$reference_field_value = $this->get_option( $field->dynamic_options_reference_field_name );
		}

		foreach ( Integrations::mailchimp()->get_list_fields( $reference_field_value ) as $field ) {
			$options[ $field['tag'] ] = "{$field['name']} - {$field['tag']}" ;
		}

		return $options;
	}


	/**
	 * @throws \Exception
	 */
	function run() {

		$list_id = $this->get_option( 'list' );
		$email = $this->get_contact_email_option();
		$field = $this->get_option( 'field' );
		$value = $this->get_option( 'value', true );

		if ( ! $list_id || ! $field || ! $email ) {
			return;
		}

		$subscriber_hash = md5( $email );

		$args = [
			'email_address' => $email,
			'merge_fields' => []
		];

		$args['merge_fields'][ $field ] = $value;

		if ( Integrations::mailchimp()->is_contact( $email, $list_id ) ) {
			Integrations::mailchimp()->request( 'PATCH', "/lists/$list_id/members/$subscriber_hash", $args );
		}
		else {
			throw new \Exception( __( 'Failed because contact did not exist.', 'automatewoo' ) );
		}

	}

}
