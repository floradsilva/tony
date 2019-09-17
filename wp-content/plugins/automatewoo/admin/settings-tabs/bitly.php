<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Settings_Tab_Bitly
 */
class Settings_Tab_Bitly extends Admin_Settings_Tab_Abstract {


	function __construct() {
		$this->id = 'bitly';
		$this->name = __( 'Bitly', 'automatewoo' );
		$this->show_tab_title = false;
	}


	function load_settings() {

		$this->section_start( 'bitly', __( 'Bitly', 'automatewoo' ),
			sprintf(
				__( 'Integrating with Bitly allows you to shorten links in your SMS messages. Create a free account at <%s>bitly.com<%s>.', 'automatewoo' ),
				'a href="https://bitly.com/"',
				'/a'
			)
		);

		$this->add_setting( 'bitly_api', [
			'type' => 'password',
			'title' => __( 'Generic Access Token', 'automatewoo' ),
			'desc_tip' => __( "Find your Generic Access Token in your Bitly account area under Your Account > Edit Profile.", 'automatewoo' ),
		]);

		$this->add_setting( 'bitly_shorten_sms_links', [
			'type' => 'checkbox',
			'title' => __( 'Shorten all SMS links', 'automatewoo' ),
		]);

		$this->section_end( 'bitly' );
	}

}

return new Settings_Tab_Bitly();
