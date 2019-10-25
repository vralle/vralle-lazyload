<?php
/**
 * The plugin settings page output
 *
 * @package    vralle-lazyload
 * @subpackage /includes/views
 */

?>
<div class="wrap">
	<h2><?php echo esc_html( $page_tile ); ?></h2>
	<form class="vll-settings-form" method="post" action="options.php">
		<?php settings_fields( $settings_group ); ?>

		<div class="vll-form-section vll--form-section--placeholder">
			<h2 class="vll-form-section-title"><?php echo esc_html__( 'Main settings', 'vralle-lazyload' ); ?></h2>
			<p class="vll-form-section-description"><?php echo esc_html__( 'You must choose where you want to use lazy loading of images.', 'vralle-lazyload' ); ?></p>
			<table class="form-table vll--form-section vll--form-section--main" role="presentation">
				<?php do_settings_fields( $settings_page, 'main' ); ?>
			</table>
		</div>

		<div class="vll-form-section vll--form-section--placeholder">
			<h2 class="vll-form-section-title"><?php echo esc_html__( 'Responsive images', 'vralle-lazyload' ); ?></h2>
			<table class="form-table vll--form-section vll--form-section--responsive" role="presentation">
				<?php do_settings_fields( $settings_page, 'responsive' ); ?>
			</table>
		</div>

		<div class="vll-form-section vll--form-section--placeholder">
			<h2 class="vll-form-section-title"><?php echo esc_html__( 'Image placeholder', 'vralle-lazyload' ); ?></h2>
			<table class="form-table vll--form-section vll--form-section--placeholder" role="presentation">
				<?php do_settings_fields( $settings_page, 'placeholder' ); ?>
			</table>
		</div>

		<div class="vll-form-section vll--form-section--ls-plugins">
			<h2 class="vll-form-section-title"><?php echo esc_html__( 'Lazyload Plugins', 'vralle-lazyload' ); ?></h2>
			<table class="form-table vll--form-section vll--form-section--ls-plugins" role="presentation">
				<?php do_settings_fields( $settings_page, 'ls-plugins' ); ?>
			</table>
		</div>

		<?php do_action( 'vll_admin_page_sections' ); ?>

		<?php submit_button(); ?>
	</form>
</div>
