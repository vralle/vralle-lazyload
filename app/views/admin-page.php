<?php
/**
 * Provide a admin area view for the plugin
 * @package    vralle-lazyload
 * @subpackage vralle-lazyload/app/views
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
