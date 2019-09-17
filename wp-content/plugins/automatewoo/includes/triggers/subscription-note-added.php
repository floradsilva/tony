<?php

namespace AutomateWoo;

defined( 'ABSPATH' ) || exit;

/***
 * Trigger_Subscription_Note_Added class.
 *
 * @since 4.5
 */
class Trigger_Subscription_Note_Added extends Trigger_Order_Note_Added {

	/**
	 * Declares data items available in trigger.
	 *
	 * @var array
	 */
	public $supplied_data_items = [ 'subscription', 'order_note', 'customer' ];

	/**
	 * Load trigger admin props.
	 */
	public function load_admin_details() {
		$this->title       = __( 'Subscription Note Added', 'automatewoo' );
		$this->description = __( 'Fires when a note is added to a subscription. This includes private notes and notes to the customer. These notes appear on the right of the subscription edit screen.', 'automatewoo' );
		$this->group       = Subscription_Workflow_Helper::get_group_name();
	}


	/**
	 * Catch comment creation hook.
	 *
	 * @param int         $comment_id
	 * @param \WP_Comment $comment
	 */
	public function catch_comment_create( $comment_id, $comment ) {

		if ( $comment->comment_type !== 'order_note' || get_post_type( $comment->comment_post_ID ) !== 'shop_subscription' ) {
			return;
		}

		$subscription = wcs_get_subscription( $comment->comment_post_ID );

		if ( ! $subscription ) {
			return;
		}

		$order_note = new Order_Note( $comment->comment_ID, $comment->comment_content, $subscription->get_id() );

		// must manually set prop because meta field is added after the comment is inserted
		$order_note->is_customer_note = $this->_is_customer_note;

		$this->maybe_run(
			[
				'customer'     => Customer_Factory::get_by_user_id( $subscription->get_user_id() ),
				'subscription' => $subscription,
				'order_note'   => $order_note,
			]
		);
	}

}
