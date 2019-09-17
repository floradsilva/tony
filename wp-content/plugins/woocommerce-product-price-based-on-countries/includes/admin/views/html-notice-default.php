<?php
/**
 * Admin View: Default Notice
 *
 * @package WCPBC/Views
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
	<p><?php echo wp_kses_post( $message ); ?></p>
</div>
