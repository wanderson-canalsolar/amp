<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$image_src = WPFD_PLUGIN_URL . '/app/admin/classes/install-wizard/content/welcome-illustration.png';
?>
<form method="post">
    <div class="start-wizard">
        <div class="start-wizard-image">
            <img src="<?php echo esc_url($image_src); ?>"
                 srcset=""
                 class="Illustration---Done" />
        </div>
        <div class="start-wizard-container">
            <div class="title h1">
                <?php esc_html_e('Welcome to WP File Download Settings first configuration wizard!', 'wpfd') ?>
            </div>
            <p class="description">
                <?php esc_html_e('This wizard will help you with some server compatibility check and with plugin main configuration. Follow some simple steps and get a powerful media library in no time', 'wpfd') ?>
            </p>
        </div>
        <div class="start-wizard-footer configuration-footer">
            <a href="<?php echo esc_url(add_query_arg('step', 'environment', remove_query_arg('activate_error')))?>" class="next-button">
                <?php esc_html_e('Continue to environment check', 'wpfd'); ?>
            </a>

            <a href="<?php echo esc_url(admin_url('admin.php?page=wpfd'))?>" class="backup-button">
                    <?php esc_html_e('I know what I\'m doing, skip wizard', 'wpfd'); ?></a>
        </div>
    </div>
</form>
