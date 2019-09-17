<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/***
 * Trigger_Order_Note_Added class.
 *
 * @since 2.2
 */
class Trigger_Order_Note_Added extends Trigger {

	/**
	 * Declares data items available in trigger.
	 *
	 * @var array
	 */
	public $supplied_data_items = [ 'order', 'order_note', 'customer' ];

	/**
	 * Prop used to cache the is_customer_note value between hooks (wc hack).
	 *
	 * @var bool
	 */
	public $_is_customer_note = false;

	/**
	 * Load trigger admin props.
	 */
	public function load_admin_details() {
		$this->title       = __( 'Order Note Added', 'automatewoo' );
		$this->description = __( 'Fires when any note is added to an order, can include both private notes and notes to the customer. These notes appear on the right of the order edit screen.', 'automatewoo' );
		$this->group       = __( 'Orders', 'automatewoo' );
	}

	/**
	 * Load trigger fields.
	 */
	public function load_fields() {
		$contains = new Fields\Text();
		$contains->set_name( 'note_contains' );
		$contains->set_title( __( 'Note contains text', 'automatewoo' ) );
		$contains->set_description( __( 'Only trigger this workflow if the order note contains the certain text. This field is optional.', 'automatewoo' ) );

		$type = new Fields\Order_Note_Type();
		$type->set_placeholder( __( '[All]', 'automatewoo' ) );

		$this->add_field( $type );
		$this->add_field( $contains );
	}

	/**
	 * Register trigger hooks.
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_new_order_note_data', [ $this, 'catch_order_note_filter' ], 20, 2 );
		add_action( 'wp_insert_comment', [ $this, 'catch_comment_create' ], 20, 2 );
	}

	/**
	 * Hooks in before 'wp_insert_comment' so we can access the 'is_customer_note' prop.
	 *
	 * @param array $data
	 * @param array $args
	 *
	 * @return array
	 */
	public function catch_order_note_filter( $data, $args ) {
		$this->_is_customer_note = $args['is_customer_note'];
		return $data;
	}


	/**
	 * Catch comment creation hook.
	 *
	 * @param int         $comment_id
	 * @param \WP_Comment $comment
	 */
	public function catch_comment_create( $comment_id, $comment ) {

		if ( $comment->comment_type !== 'order_note' || get_post_type( $comment->comment_post_ID ) !== 'shop_order' ) {
			return;
		}

		$order = wc_get_order( $comment->comment_post_ID );

		if ( ! $order ) {
			return;
		}

		$order_note = new Order_Note( $comment->comment_ID, $comment->comment_content, $order->get_id() );

		// must manually set prop because meta field is added after the comment is inserted
		$order_note->is_customer_note = $this->_is_customer_note;

		$this->maybe_run(
			[
				'customer'   => Customer_Factory::get_by_order( $order ),
				'order'      => $order,
				'order_note' => $order_note,
			]
		);
	}


	/**
	 * Validate a workflow.
	 *
	 * This method is also used by the subscription note added trigger.
	 *
	 * @param Workflow $workflow
	 *
	 * @return bool
	 */
	public function validate_workflow( $workflow ) {

		$order_note = $workflow->data_layer()->get_order_note();

		if ( ! $order_note ) {
			return false;
		}

		$note_type     = $workflow->get_trigger_option( 'note_type' );
		$note_contains = $workflow->get_trigger_option( 'note_contains' );

		if ( $note_type ) {
			if ( $order_note->get_type() !== $note_type ) {
				return false;
			}
		}

		if ( $note_contains ) {
			if ( ! stristr( $order_note->content, $note_contains ) ) {
				return false;
			}
		}

		return true;
	}

}
