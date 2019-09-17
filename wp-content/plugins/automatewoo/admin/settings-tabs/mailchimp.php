<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Settings_Tab_Mailchimp
 */
class Settings_Tab_Mailchimp extends Admin_Settings_Tab_Abstract {

	function __construct() {
		$this->id = 'mailchimp';
		$this->name = __( 'MailChimp', 'automatewoo' );
	}

	/**
	 * @return array
	 */
	function get_settings() {
		return [
			[
				'type' 	=> 'title',
				'id' 	=> 'automatewoo_mailchimp_integration',
				'desc' => __( 'Enabling the MailChimp integration does not automatically sync your data but makes three actions available when creating workflows. These actions can be used to automate how you add and remove members from your MailChimp lists and update your custom fields.', 'automatewoo' )
			],

			[
				'title' => __( 'Enable integration', 'woocommerce' ),
				'id' => 'automatewoo_mailchimp_integration_enabled',
				'default' => 'no',
				'autoload' => true,
				'type' => 'checkbox',
			],

			[
				'title' => __( 'API Key', 'automatewoo' ),
				'id' => 'automatewoo_mailchimp_api_key',
				'tooltip' => __( 'You can get your API key when logged in to MailChimp under Account > Extras > API Keys.', 'automatewoo' ),
				'type' => 'password',
				'autoload' => false,
			],

			[
				'type' 	=> 'sectionend',
				'id' 	=> 'automatewoo_mailchimp_integration'
			]

		];
	}


	function save() {

		$is_valid = self::validate_key();

		if ( $is_valid ) {
			$mailchimp = Integrations::mailchimp();

			if ( $mailchimp ) {
				$mailchimp->clear_cache_data();
			}

			parent::save();
		}

	}


	function validate_key() {
		$api_key = aw_get_post_var( 'automatewoo_mailchimp_api_key' );

		$err_msg = __( 'The MailChimp API Key you entered is not valid.' , 'automatewoo' );

		// return true if empty so users can wipe out their key.
		if ( $api_key == '' ) {
			return true;
		}

		if ( false == $api_key ) {
			parent::add_error( $err_msg );

			return false;
		}

		if ( strpos( $api_key, '-' ) === false ) {
			parent::add_error( $err_msg );

			return false;
		}

		$mailchimp_test = new Integration_Mailchimp( $api_key );

		$response = $mailchimp_test->request( 'GET', '/' );

		if ( $response->is_successful() ) {
			return true;
		} else {
			parent::add_error( $err_msg );

			return false;
		}
	}
}

return new Settings_Tab_Mailchimp();
