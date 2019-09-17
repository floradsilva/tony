<?php
// phpcs:ignoreFile

namespace AutomateWoo\Event_Helpers;

use AutomateWoo\Events;
use AutomateWoo\Review_Factory;

/**
 * @class Review_Posted
 */
class Review_Posted {


	static function init() {
		add_action( 'comment_post', [ __CLASS__, 'catch_new_comments' ], 20, 2 );
		add_action( 'transition_comment_status', [ __CLASS__, 'catch_comment_approval' ], 20, 3 );
	}


	/**
	 * Catch any comments approved on creation
	 *
	 * @param $comment_ID
	 * @param $approved
	 */
	static function catch_new_comments( $comment_ID, $approved ) {
		if ( $approved == 1 ) {
			self::maybe_dispatch_event( get_comment( $comment_ID ) );
		}
	}


	/**
	 * Catch any comments that were approved after creation
	 *
	 * @param $new_status string
	 * @param $old_status string
	 * @param $comment object
	 */
	static function catch_comment_approval( $new_status, $old_status, $comment ) {
		if ( $new_status === 'approved' ) {
			self::maybe_dispatch_event( $comment );
		}
	}


	/**
	 * @param $comment \WP_Comment|object
	 */
	static function maybe_dispatch_event( $comment ) {
		$review = Review_Factory::get( $comment );

		// validates if the comment is actually a review
		if ( ! $review ) {
			return;
		}

		do_action( 'automatewoo/review/posted', $review );
		Events::schedule_async_event( 'automatewoo/review/posted_async', [ $review->get_id() ] );
	}

}
