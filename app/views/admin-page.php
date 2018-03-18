<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      0.1.0
 *
 * @package    Vr_Lazyload
 * @subpackage Vr_Lazyload/app/views
 */
?>

<div class="wrap">
    <h2><?php echo \get_admin_page_title(); ?></h2>
    <?php \settings_errors($this->plugin_name); ?>
    <form method="post" action="options.php">
        <?php
        // Output nonce, action, and option_page fields for a settings page.
        \settings_fields($this->plugin_name);
        // Prints out all settings sections added to a particular settings page
        \do_settings_sections($this->plugin_name);
        \submit_button();
        ?>
    </form>
</div>
