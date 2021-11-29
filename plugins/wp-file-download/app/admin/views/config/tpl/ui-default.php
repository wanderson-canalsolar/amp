<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 4.4
 */

// No direct access.
defined('ABSPATH') || die();
use Joomunited\WPFramework\v1_0_5\Application;
?>
<div class="ju-main-wrapper">
    <div class="ju-left-panel-toggle">
        <i class="dashicons dashicons-leftright ju-left-panel-toggle-icon"></i>
    </div>
    <div class="ju-left-panel">
        <?php
        /**
         * Action print out configuration menu
         *
         * @hook wpfd_admin_ui_menu_logo - 10
         *
         * @internal
         */
        do_action('wpfd_admin_ui_configuration_menu');
        ?>
    </div>

    <div class="ju-right-panel">
        <?php
        /**
         * Action to write import notice
         *
         * @ignore
        */
        do_action('wpdf_admin_notices');

        /**
         * Action print out configuration content
         *
         * @internal
         */
        do_action('wpfd_admin_ui_configuration_content');
        ?>
    </div>
</div>
<script type="text/javascript">
    wpfdajaxurl = "<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl());  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>";
</script>
