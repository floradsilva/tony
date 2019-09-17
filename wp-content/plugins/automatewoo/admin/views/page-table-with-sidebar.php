<?php
// phpcs:ignoreFile
/**
 * @package AutomateWoo/Admin/Views
 * @since 2.7.8
 *
 * @var string $page
 * @var string $sidebar_content
 * @var string $messages
 * @var AutomateWoo\Admin\Controllers\Base $controller
 * @var AutomateWoo\Admin_List_Table $table
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wrap automatewoo-page automatewoo-page--<?php echo $page ?>">

	<?php $controller->output_view( 'page-heading' ); ?>

	<?php echo $messages ?>

	<div class="automatewoo-content automatewoo-content--has-sidebar">

		<?php if ( isset( $sidebar_content ) ): ?>
			<div class="automatewoo-sidebar">
				<?php echo $sidebar_content ?>
			</div>
		<?php endif; ?>

		<div class="automatewoo-main">
			<?php $table->display() ?>
		</div>

	</div>

</div>
