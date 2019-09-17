<?php
// phpcs:ignoreFile

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @var Workflow $workflow
 */
?>

	<table class="automatewoo-table">

		<tr class="automatewoo-table__row">
			<td class="automatewoo-table__col">
				<label class="automatewoo-label automatewoo-label--inline-checkbox">
						 <?php _e( 'Is transactional?', 'automatewoo' ) ?>
						 <?php
						 $field = new Fields\Checkbox();
						 $field
							 ->set_name_base('aw_workflow_data')
							 ->set_name('is_transactional')
							 ->render( $workflow ? absint( $workflow->get_meta('is_transactional') ) : '' );
						 ?>
						 <?php echo Admin::help_tip( __( "Is this workflow used for transactional emails instead of marketing emails? Checking this removes the unsubscribe link in email.", 'automatewoo' ) ) ?>
				</label>
			</td>
		</tr>


		<tr class="automatewoo-table__row">
			<td class="automatewoo-table__col">

				<label class="automatewoo-label automatewoo-label--inline-checkbox">
					<?php _e( 'Enable tracking', 'automatewoo' ) ?>
					<?php
					$field = new Fields\Checkbox();
					$field
						->set_name_base('aw_workflow_data[workflow_options]')
						->set_name('click_tracking')
						->add_classes('aw-checkbox-enable-click-tracking')
						->render( $workflow ? $workflow->get_option('click_tracking') : '' );
					?>
					<?php echo Admin::help_tip( __( "Enables open and click tracking on emails sent from this workflow. SMS messages will also have click tracking but open tracking is not possible with SMS.", 'automatewoo' ) ) ?></label>
			</td>
		</tr>


		<tr class="automatewoo-table__row js-require-email-tracking">
			<td class="automatewoo-table__col">

				<label class="automatewoo-label automatewoo-label--inline-checkbox">
					<?php _e( 'Enable conversion tracking', 'automatewoo' ) ?>
					<?php
					$field = new Fields\Checkbox();
					$field
						->set_name_base('aw_workflow_data[workflow_options]')
						->set_name('conversion_tracking')
						->add_classes('aw-checkbox-enable-conversion-tracking')
						->render( $workflow ? $workflow->get_option('conversion_tracking') : '' );
					?>
					<?php echo Admin::help_tip( __( "Enables conversion tracking on purchases made as a result of this workflow.", 'automatewoo' ) ) ?>
				</label>

			</td>
		</tr>


		<tr class="automatewoo-table__row js-require-email-tracking">
			<td class="automatewoo-table__col">

				<label class="automatewoo-label"><?php _e( 'Google Analytics link tracking', 'automatewoo' ) ?> <?php echo Admin::help_tip( __('This will be appended to every URL in the email content or SMS body.', 'automatewoo' ) ) ?> </label>

				<?php
				$field = new Fields\Text_Area();
				$field
					->set_rows(3)
					->set_name_base('aw_workflow_data[workflow_options]')
					->set_name('ga_link_tracking')
					->add_classes('automatewoo-field--monospace')
					->add_extra_attr('spellcheck', 'false')
					->set_placeholder( 'e.g. utm_source=automatewoo&utm_medium=email&utm_campaign=example' )
					->render( $workflow ? $workflow->get_option('ga_link_tracking') : '' )
				?>
			</td>
		</tr>


		<tr class="automatewoo-table__row">
			<td class="automatewoo-table__col">

				<label class="automatewoo-label"><?php _e( 'Workflow order', 'automatewoo' ) ?> <?php echo Admin::help_tip( __( 'The order that workflows will run.', 'automatewoo' ) ) ?></label>

				<?php
				global $post;

				$field = new Fields\Number();
				$field
					->set_name('menu_order')
					->render( $post ? $post->menu_order : '' )
				?>
			</td>
		</tr>

	</table>
