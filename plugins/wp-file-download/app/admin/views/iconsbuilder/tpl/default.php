<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0W
 */

use Joomunited\WPFramework\v1_0_5\Application;

// No direct access.
defined('ABSPATH') || die();

?>
<div class="ju-main-wrapper">
    <div class="ju-left-panel-toggle">
        <i class="dashicons dashicons-leftright ju-left-panel-toggle-icon"></i>
    </div>
    <div class="ju-left-panel">
        <?php
        /**
         * Action print out icons builder menu
         *
         * @hook wpfd_admin_ui_menu_logo - 10
         *
         * @internal
         */
        do_action('wpfd_admin_ui_icons_builder_menu');
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
         * Action print out icons builder content
         *
         * @internal
         */
        do_action('wpfd_admin_ui_icons_builder_content');
        ?>
    </div>
</div>
<script type="text/javascript">
    wpfdajaxurl = "<?php echo wpfd_sanitize_ajax_url(Application::getInstance('Wpfd')->getAjaxUrl());  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- keep this, if not it error ?>";
    wpfdsvgspires = "<?php echo esc_url(WPFD_PLUGIN_URL . '/app/site/assets/icons/svgicons/wpfd-svgs.svg'); ?>";
</script>
