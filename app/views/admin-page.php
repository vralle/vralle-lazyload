<?php
/**
 * Provide a admin area view for the plugin
 *
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app/views
 */

?>
<div class="wrap">
	<h2><?php echo \esc_html( get_admin_page_title() ); ?></h2>
	<?php \settings_errors( $this->plugin_name ); ?>
	<form method="post" action="options.php">
		<?php
		\settings_fields( $this->plugin_name );
		\do_settings_sections( $this->plugin_name );
		\submit_button();
		?>
	</form>
</div>
