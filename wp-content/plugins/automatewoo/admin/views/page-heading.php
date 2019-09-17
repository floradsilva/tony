<?php
// phpcs:ignoreFile
/**
 * @var AutomateWoo\Admin\Controllers\Base $controller
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>


<h1 class="wp-heading-inline"><?php echo esc_attr( $controller->get_heading() ); ?></h1>

<?php foreach( $controller->get_heading_links() as $link => $title ): ?>
	<a href="<?php echo esc_url( $link ) ?>" class="page-title-action"><?php echo esc_attr( $title ) ?></a>
<?php endforeach; ?>

<hr class="wp-header-end">
