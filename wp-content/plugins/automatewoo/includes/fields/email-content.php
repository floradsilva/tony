<?php
// phpcs:ignoreFile

namespace AutomateWoo\Fields;

use AutomateWoo\Clean;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Email_Content
 */
class Email_Content extends Field {

	protected $name = 'email_content';

	protected $type = 'email-content';


	function __construct() {
		parent::__construct();
		$this->set_title( __( 'Email content', 'automatewoo' ) );
		$this->set_description(__( 'The contents of this field will be formatted as per the selected email template.', 'automatewoo' ));
	}


	/**
	 * @param string $value
	 */
	function render( $value ) {
		$id = uniqid();
		$value = Clean::email_content( $value );

		wp_editor( $value, $id, [
			'textarea_name' => $this->get_full_name(),
			'tinymce' => true, // default to visual
			'quicktags' => true,
		]);

		if ( is_ajax() ) {
			$this->ajax_init( $id );
		}
	}


	/**
	 * @param $id
	 */
	function ajax_init( $id ) {
		?>
		<script type="text/javascript">
			(function(){
				AutomateWoo.Workflows.init_ajax_wysiwyg('<?php echo $id; ?>');
			}());
		</script>
		<?php
	}


	/**
	 * Sanitizes the value of the field.
	 *
	 * @since 4.4.0
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	function sanitize_value( $value ) {
		return Clean::email_content( $value );
	}


}
