<?php
// phpcs:ignoreFile

namespace AutomateWoo\Compat;

/**
 * Provide order note methods that are compatible with changes introduced in WC 3.2.
 *
 * @class Order_Notes
 * @since 4.4
 */
class Order_Notes {

	/**
	 * Get order notes.
	 *
	 * @param  array $args Query arguments {
	 *     Array of query parameters.
	 *
	 *     @type string $limit         Maximum number of notes to retrieve.
	 *                                 Default empty (no limit).
	 *     @type int    $order_id      Limit results to those affiliated with a given order ID.
	 *                                 Default 0.
	 *     @type array  $order__in     Array of order IDs to include affiliated notes for.
	 *                                 Default empty.
	 *     @type array  $order__not_in Array of order IDs to exclude affiliated notes for.
	 *                                 Default empty.
	 *     @type string $orderby       Define how should sort notes.
	 *                                 Accepts 'date_created', 'date_created_gmt' or 'id'.
	 *                                 Default: 'id'.
	 *     @type string $order         How to order retrieved notes.
	 *                                 Accepts 'ASC' or 'DESC'.
	 *                                 Default: 'DESC'.
	 *     @type string $type          Define what type of note should retrieve.
	 *                                 Accepts 'customer', 'internal' or empty for both.
	 *                                 Default empty.
	 * }
	 * @return \stdClass[]              Array of stdClass objects with order notes details.
	 */
	static function get_order_notes( $args ) {

		if ( version_compare( WC()->version, '3.2', '<' ) ) {

			$key_mapping = array(
				'limit'         => 'number',
				'order_id'      => 'post_id',
				'order__in'     => 'post__in',
				'order__not_in' => 'post__not_in',
			);

			foreach ( $key_mapping as $query_key => $db_key ) {
				if ( isset( $args[ $query_key ] ) ) {
					$args[ $db_key ] = $args[ $query_key ];
					unset( $args[ $query_key ] );
				}
			}

			// Define orderby.
			$orderby_mapping = array(
				'date_created'     => 'comment_date',
				'date_created_gmt' => 'comment_date_gmt',
				'id'               => 'comment_ID',
			);

			$args['orderby'] = ! empty( $args['orderby'] ) && in_array( $args['orderby'], array( 'date_created', 'date_created_gmt', 'id' ), true ) ? $orderby_mapping[ $args['orderby'] ] : 'comment_ID';

			// Set WooCommerce order type.
			if ( isset( $args['type'] ) && 'customer' === $args['type'] ) {
				$args['meta_query'] = array(
					array(
						'key'     => 'is_customer_note',
						'value'   => 1,
						'compare' => '=',
					),
				);
			} elseif ( isset( $args['type'] ) && 'internal' === $args['type'] ) {
				$args['meta_query'] = array(
					array(
						'key'     => 'is_customer_note',
						'compare' => 'NOT EXISTS',
					),
				);
			}

			// Set correct comment type.
			$args['type'] = 'order_note';

			// Always approved.
			$args['status'] = 'approve';

			// Does not support 'count' or 'fields'.
			unset( $args['count'], $args['fields'] );

			remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

			$notes = get_comments( $args );

			add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );

			return array_filter( array_map( 'AutomateWoo\Compat\Order_Notes::get_order_note', $notes ) );
		} else {
			return wc_get_order_notes( $args );
		}
	}


	/**
	 * Get an order note
	 *
	 * @param  int|WP_Comment $data Note ID (or WP_Comment instance for internal use only).
	 * @return stdClass|null Object with order note details or null when does not exists.
	 */
	static function get_order_note( $data ) {

		if ( version_compare( WC()->version, '3.2', '<' ) ) {

			if ( is_numeric( $data ) ) {
				$data = get_comment( $data );
			}

			if ( ! is_a( $data, 'WP_Comment' ) ) {
				return null;
			}

			return (object) array(
				'id'            => (int) $data->comment_ID,
				'date_created'  => aw_string_to_wc_datetime( $data->comment_date ),
				'content'       => $data->comment_content,
				'customer_note' => (bool) get_comment_meta( $data->comment_ID, 'is_customer_note', true ),
				'added_by'      => __( 'WooCommerce', 'automatewoo' ) === $data->comment_author ? 'system' : $data->comment_author,
			);
		} else {
			return wc_get_order_note( $data );
		}
	}
}
