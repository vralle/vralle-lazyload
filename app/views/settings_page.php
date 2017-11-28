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
    <h2><?php echo get_admin_page_title(); ?></h2>
    <?php settings_errors(); ?>
    <form method="post" action="options.php">
        <?php
        // Do settings group
        settings_fields(self::PLUGIN_OPTION['group']);
        // Do Page ID (slug)
        do_settings_sections(self::PLUGIN_OPTION['admin_page']);
        submit_button();
        ?>
    </form>
</div>
