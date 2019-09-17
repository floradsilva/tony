<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @var $iframe_url string
 * @var $type string
 * @var $email_subject string
 * @var $template string
 * @var $args array
 */

if ( ! $test_emails = get_user_meta( get_current_user_id(), 'automatewoo_email_preview_test_emails', true ) ) {
	$user = wp_get_current_user();
	$test_emails = $user->user_email;
}

?>

<div class="aw-preview">
	<div class="aw-preview__header">

		<div class="aw-preview__header-left">
			<div class="from"><strong><?php _e('From', 'automatewoo') ?>:</strong> <?php echo Emails::get_from_name( $template ) ?> &lt;<?php echo Emails::get_from_address( $template ) ?>&gt;</div>
			<div class="from"><strong><?php _e('Subject', 'automatewoo') ?>:</strong> <?php echo $email_subject ?></div>
		</div>

		<div class="aw-preview__header-right">

			<form class="aw-preview__send-test-form">
				<input type="text" value="<?php echo $test_emails ?>" name="to_emails" class="email-input" placeholder="<?php _e( 'Comma separate emails...', 'automatewoo') ?>">
				<input type="hidden" name="type" value="<?php echo esc_attr( $type ) ?>">
				<input type="hidden" name="args" value='<?php echo json_encode( $args ) ?>'>

				<button type="submit" class="button-secondary"><?php _e('Send', 'automatewoo') ?></button>
			</form>

		</div>

	</div>

	<iframe class="aw-preview__email-iframe" src="<?php echo $iframe_url ?>" width="100%" frameborder="0"></iframe>
</div>
