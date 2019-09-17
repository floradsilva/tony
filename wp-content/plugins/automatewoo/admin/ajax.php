<?php
// phpcs:ignoreFile

namespace AutomateWoo;

use AutomateWoo\Admin\JSON_Search;

/**
 * @class Admin_Ajax
 */
class Admin_Ajax {

	/**
	 * Hook in methods
	 */
	static function init() {
		$ajax_events = [
			'fill_trigger_fields',
			'fill_action_fields',
			'json_search_workflows',
			'json_search_coupons',
			'json_search_attribute_terms',
			'json_search_taxonomy_terms',
			'json_search_customers',
			'json_search_products_and_variations_not_variable',
			'json_search_products_not_variations_not_variable',
			'activate',
			'deactivate',
			'email_preview_iframe',
			'test_sms',
			'database_update',
			'database_update_items_to_process_count',
			'save_preview_data',
			'send_test_email',
			'dismiss_expiry_notice',
			'dismiss_system_error_notice',
			'dismiss_license_nag_notice',
			'get_rule_select_choices',
			'toggle_workflow_status',
			'modal_log_info',
			'modal_queue_info',
			'modal_variable_info',
			'modal_cart_info',
			'update_dynamic_action_select'
		];

		foreach ( $ajax_events as $ajax_event ) {
			add_action( 'wp_ajax_aw_' . $ajax_event, [ __CLASS__, $ajax_event ] );
		}
	}


	/**
	 *
	 */
	static function fill_trigger_fields() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		$trigger_name = Clean::string( aw_request('trigger_name') );
		$workflow_id = absint( aw_request('workflow_id') );
		$is_new_workflow = aw_request('is_new_workflow');

		$workflow = false;
		$trigger = Triggers::get( $trigger_name );

		if ( ! $trigger )
			die;

		if ( ! $is_new_workflow ) {
			$workflow = new Workflow( $workflow_id );
		}

		ob_start();

		Admin::get_view('trigger-fields', [
			'trigger' => $trigger,
			'workflow' => $workflow,
		]);

		$fields = ob_get_clean();

		wp_send_json_success([
			'fields' => $fields,
			'trigger' => Admin_Workflow_Edit::get_trigger_data( $trigger ),
		]);
	}


	/**
	 *
	 */
	static function fill_action_fields() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		$action_name = Clean::string( aw_request('action_name') );
		$action_number = Clean::string( aw_request('action_number') );

		$action = Actions::get( $action_name );

		ob_start();

		Admin::get_view( 'action-fields', [
			'action' => $action,
			'action_number' => $action_number,
		]);

		$fields = ob_get_clean();

		wp_send_json_success([
			'fields' => $fields,
			'title' => $action->get_title( true ),
			'description' => $action->get_description_html()
		]);
	}


	/**
	 * Search for workflows and echo JSON.
	 */
	public static function json_search_workflows() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		ob_start();

		$term = Clean::string( wp_unslash( aw_get_url_var( 'term' ) ) );
		JSON_Search::workflows( $term );
	}


	/**
	 * Search customers, includes guests customers
	 */
	static function json_search_customers() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		ob_start();

		$term = Clean::string( wp_unslash( aw_get_url_var( 'term' ) ) );
		JSON_Search::customers( $term );
	}

	/**
	 * Search for products and variations, but not variable products.
	 */
	static function json_search_products_and_variations_not_variable() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$term = Clean::string( wp_unslash( aw_get_url_var( 'term' ) ) );
		JSON_Search::products( $term, true, false );
	}

	/**
	 * Search for products excluding variable and variation products.
	 */
	static function json_search_products_not_variations_not_variable() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$term = Clean::string( wp_unslash( aw_get_url_var( 'term' ) ) );
		JSON_Search::products( $term, false, false );
	}


	/**
	 * Search for products and echo json
	 */
	public static function json_search_attribute_terms() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		if ( empty( $_GET['term'] ) || empty( $_GET['sibling'] ) ) {
			die;
		}

		$search = Clean::string( stripslashes( $_GET['term'] ) );
		$sibling = Clean::string( stripslashes( $_GET['sibling'] ) );

		$terms = get_terms( 'pa_' . $sibling, [
			'hide_empty' => false,
			'search' => $search
		]);

		$found = [];

		if ( ! $terms || is_wp_error($terms)  )
			die();

		foreach ( $terms as $term ) {
			$found[ $term->term_id . '|' . $term->taxonomy  ] = rawurldecode( $term->name );
		}

		wp_send_json( $found );
	}



	/**
	 * Search for products and echo json
	 */
	public static function json_search_taxonomy_terms() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		ob_start();

		$search = Clean::string( stripslashes( $_GET['term'] ) );
		$sibling = Clean::string( stripslashes( $_GET['sibling'] ) );

		if ( empty( $search ) || empty($sibling) ) {
			die;
		}

		$terms = get_terms( $sibling, [
			'hide_empty' => false,
			'search' => $search
		]);


		$found = [];

		if ( ! $terms || is_wp_error($terms)  )
			die;

		foreach ( $terms as $term ) {
			$found[ $term->term_id . '|' . $term->taxonomy  ] = rawurldecode( $term->name );
		}

		wp_send_json( $found );
	}

	/**
	 * Search for coupons and echo JSON.
	 */
	public static function json_search_coupons() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$term = Clean::string( wp_unslash( aw_get_url_var( 'term' ) ) );
		JSON_Search::coupons( $term, true );
	}

	static function email_preview_iframe() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$type = Clean::string( aw_request('type') );
		$args = Clean::recursive( aw_request('args') );

		switch ( $type ) {

			case 'workflow_action':
				if ( ! $action = Preview_Data::generate_preview_action( $args['workflow_id'], $args['action_number'] ) )
					die();

				if ( ! $action || ! $action->can_be_previewed() ) {
					wp_die( __( 'Sorry, this action can not be previewed.', 'automatewoo' ) );
				}

				do_action( 'automatewoo/action/before_preview', $action );

				$action->workflow->setup();

				echo $action->preview();

				$action->workflow->cleanup();

				do_action( 'automatewoo/action/after_preview', $action );

				break;

			default:
				do_action( 'automatewoo/email_preview/html', $type, $args );
		}

		exit();
	}


	/**
	 * Sends a test to supplied emails
	 */
	static function send_test_email() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$type = Clean::string( aw_request('type') );
		$args = Clean::recursive( aw_request('args') );
		$to = Clean::string( aw_request('to_emails') );

		// save the to field
		update_user_meta( get_current_user_id(), 'automatewoo_email_preview_test_emails', $to );

		$to = Emails::parse_multi_email_field( $to );

		switch ( $type ) {

			case 'workflow_action':

				if ( ! $action = Preview_Data::generate_preview_action( $args['workflow_id'], $args['action_number'], 'test' ) )
					die();

				if ( ! $action || ! $action->can_be_previewed() ) {
					wp_die( __( 'Sorry, this action can not be previewed.', 'automatewoo' ) );
				}

				$action->workflow->setup();

				$result = $action->send_test( $to );

				$action->workflow->cleanup();

				break;

			default:
				do_action( 'automatewoo/email_preview/send_test', $type, $to, $args );
				$result = false;
		}

		if ( $result instanceof \WP_Error ) {
			wp_send_json_error([
				'message' => __( 'Error: ', 'automatewoo' ) . $result->get_error_message(),
			]);
		}

		wp_send_json_success([
			'message' => sprintf(
				__( 'Success! %s email%s sent.', 'automatewoo' ),
				count($to),
				count($to) == 1 ? '' : 's'
			)
		]);
	}



	static function test_sms() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$from = Clean::string( aw_request('from') );
		$auth_id = Clean::string( aw_request('auth_id') );
		$auth_token = Clean::string( aw_request('auth_token') );
		$test_message = Clean::string( aw_request('test_message') );
		$test_recipient = Clean::string( aw_request('test_recipient') );

		$twilio = new Integration_Twilio( $from, $auth_id, $auth_token );

		$twilio->log_errors = false; // errors will be visible

		$request = $twilio->send_sms( $test_recipient, $test_message, $from );

		if ( $request->is_successful() ) {
			wp_send_json_success( [
				'message' => __('Message sent.','automatewoo')
			] );
		}
		else {
			wp_send_json_error( [
				'message' => $twilio->get_request_error_message( $request )
			] );
		}
	}


	static function database_update() {

		$verify = wp_verify_nonce( $_REQUEST['nonce'], 'automatewoo_database_upgrade' );
		$plugin_slug = Clean::string( aw_request('plugin_slug') );

		if ( ! $verify ) {
			wp_send_json_error( __( 'Permission error.', 'automatewoo' ) );
		}

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		if ( $plugin_slug == AW()->plugin_slug ) {
			// updating the primary plugin
			$complete = Installer::run_database_updates();

			wp_send_json_success([
				'complete' => $complete,
				'items_processed' => Installer::$db_update_items_processed
			]);
		}
		else {
			// updating an addon
			$addon = Addons::get( $plugin_slug );

			if ( ! $addon ) {
				wp_send_json_error(__( 'Add-on could not be updated', 'automatewoo' ) );
			}

			$addon->do_database_update();

			wp_send_json_success([
				'complete' => true
			]);
		}
	}


	static function database_update_items_to_process_count() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$plugin_slug = Clean::string( aw_request('plugin_slug') );

		if ( $plugin_slug == AW()->plugin_slug ) {
			$count = Installer::get_database_update_items_to_process_count();
		}
		else {
			$count = 0; // batch processor not yet supported for addons
		}

		wp_send_json_success([
			'items_to_process' => $count
		]);
	}


	/**
	 * To preview an action save temporarily in the options table.
	 */
	static function save_preview_data() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$workflow = Workflow_Factory::get( aw_get_post_var( 'workflow_id' ) );
		$trigger_name = Clean::string( aw_get_post_var( 'trigger_name' ) );
		$action_fields = $workflow->sanitize_action_fields( aw_get_post_var( 'action_fields' ) );

		if ( ! $trigger_name || ! is_array( $action_fields ) || ! $workflow ) {
			wp_send_json_error();
		}

		$preview_data = [
			'trigger_name' => $trigger_name,
			'action_fields' => $action_fields,
		];

		update_option( 'aw_wf_preview_data_' . $workflow->get_id(), $preview_data, false );

		wp_send_json_success();
	}


	/**
	 * Dismisses expiry notice for 6 months.
	 */
	static function dismiss_expiry_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		set_transient( 'aw_dismiss_licence_expiry_notice', '1', 6 * MONTH_IN_SECONDS );

		wp_send_json_success();
	}


	/**
	 *
	 */
	static function dismiss_system_error_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		delete_transient('automatewoo_background_system_check_errors');

		wp_send_json_success();
	}


	/**
	 * Dismisses license nag notice for 6 months.
	 */
	static function dismiss_license_nag_notice() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		set_transient( 'automatewoo_dismiss_license_nag_notice', '1', 6 * MONTH_IN_SECONDS );

		wp_send_json_success();
	}



	static function get_rule_select_choices() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		if ( ! $rule_name = Clean::string( aw_request('rule_name') ) )
			die;

		$rule_object = Rules::get( $rule_name );

		if ( $rule_object->type == 'select' ) {
			wp_send_json_success([
				'select_choices' => $rule_object->get_select_choices()
			]);
		}

		die;
	}


	/**
	 * Display content for log details modal
	 */
	static function modal_log_info() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		if ( $log = AW()->get_log( absint( aw_request('log_id') ) ) ) {
			Admin::get_view( 'modal-log-info', [ 'log' => $log ] );
			die;
		}

		die( __( 'No log found.', 'automatewoo' ) );
	}


	static function modal_queue_info() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		if ( $event = AW()->get_queued_event( absint( aw_request('queued_event_id') ) ) ) {
			Admin::get_view( 'modal-queued-event-info', [ 'event' => $event ] );
			die;
		}

		die( __( 'No queued event found.', 'automatewoo' ) );
	}


	static function modal_variable_info() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			die;
		}

		$variable = Variables::get_variable( Clean::string( aw_request( 'variable' ) ) );

		if ( $variable ) {
			Admin::get_view( 'modal-variable-info', [
				'variable' => $variable
			]);
			die;
		}

		wp_die( __( 'Variable not found.', 'automatewoo' ) );
	}


	static function modal_cart_info() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		if ( $cart = AW()->get_cart( absint( aw_request('cart_id') ) ) ) {
			Admin::get_view( 'modal-cart-info', [ 'cart' => $cart ] );
			die;
		}

		die( __( 'No cart found.', 'automatewoo' ) );
	}



	static function toggle_workflow_status() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		$workflow = Workflow_Factory::get( aw_request( 'workflow_id' ) );
		$new_state = Clean::string( aw_request( 'new_state' ) );

		if ( ! $workflow || ! $new_state )
			die;

		$workflow->update_status( $new_state === 'on' ? 'active' : 'disabled' );

		wp_send_json_success();
	}



	static function update_dynamic_action_select() {

		if ( ! current_user_can( 'manage_woocommerce' ) )
			die;

		$action_name = Clean::string( aw_request( 'action_name' ) );
		$target_field_name = Clean::string( aw_request( 'target_field_name' ) );
		$reference_field_value = Clean::string( aw_request( 'reference_field_value' ) );

		$options = [];

		if ( $reference_field_value ) {
			$action = Actions::get( $action_name );
			$options = $action->get_dynamic_field_options( $target_field_name, $reference_field_value );
		}

		wp_send_json_success( $options );
	}

}
