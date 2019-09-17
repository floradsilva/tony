<?php
// phpcs:ignoreFile

namespace AutomateWoo;

/**
 * @class Constants
 */
class Constants {


	static function init() {
		self::set_defaults();
	}


	static function set_defaults() {

		if ( ! defined('AW_PREVENT_WORKFLOWS') ) {
			define( 'AW_PREVENT_WORKFLOWS', false );
		}

		if ( ! defined('AUTOMATEWOO_DISABLE_ASYNC_CUSTOMER_NEW_ACCOUNT') ) {
			define( 'AUTOMATEWOO_DISABLE_ASYNC_CUSTOMER_NEW_ACCOUNT', false );
		}

		if ( ! defined('AUTOMATEWOO_DISABLE_ASYNC_SUBSCRIPTION_STATUS_CHANGED') ) {
			define( 'AUTOMATEWOO_DISABLE_ASYNC_SUBSCRIPTION_STATUS_CHANGED', false );
		}

		if ( ! defined('AUTOMATEWOO_DISABLE_ASYNC_ORDER_STATUS_CHANGED') ) {
			define( 'AUTOMATEWOO_DISABLE_ASYNC_ORDER_STATUS_CHANGED', false );
		}

		if ( ! defined('AUTOMATEWOO_LOG_ASYNC_EVENTS' ) ) {
			define( 'AUTOMATEWOO_LOG_ASYNC_EVENTS', false );
		}

		if ( ! defined('AUTOMATEWOO_ENABLE_INSTANT_EVENT_DISPATCHING' ) ) {
			define( 'AUTOMATEWOO_ENABLE_INSTANT_EVENT_DISPATCHING', false );
		}

		if ( ! defined('AUTOMATEWOO_LOG_SENT_SMS' ) ) {
			define( 'AUTOMATEWOO_LOG_SENT_SMS', false );
		}

		if ( ! defined('AUTOMATEWOO_BACKGROUND_PROCESS_DEBUG' ) ) {
			define( 'AUTOMATEWOO_BACKGROUND_PROCESS_DEBUG', false );
		}

	}

}
