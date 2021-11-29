<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$image_src = WPFD_PLUGIN_URL . '/app/admin/classes/install-wizard/content/checklist-icon.png';
$srcset2x = WPFD_PLUGIN_URL . '/app/admin/classes/install-wizard/content/done/done-illustration@2x.png';
$srcset3x = WPFD_PLUGIN_URL . '/app/admin/classes/install-wizard/content/done/done-illustration@3x.png';
?>
<div class="wizard-content-done">
    <div class="wizard-done">
        <div class="wizard-done-image">
            <img src="<?php echo esc_url(WPFD_PLUGIN_URL . '/app/admin/classes/install-wizard/content/done/done-illustration.png'); ?>"
                 srcset="<?php echo esc_url($srcset2x); ?> 2x,<?php echo esc_url($srcset3x); ?> 3x" class="Illustration---Done">

        </div>
        <div class="wizard-done-container">
            <div class="title h1"><?php esc_html_e('Done', 'wpfd') ?></div>
            <p class="description">
                <?php esc_html_e('You have now completed the plugin quick configuration', 'wpfd') ?>
            </p>
        </div>
        <div class="wizard-done-footer configuration-footer">
            <a href="<?php echo esc_url(admin_url('admin.php?page=wpfd')) ?>" class="button">
                <?php esc_html_e('GO TO WP FILE DOWNLOAD', 'wpfd'); ?></a>
        </div>
    </div>
</div>
