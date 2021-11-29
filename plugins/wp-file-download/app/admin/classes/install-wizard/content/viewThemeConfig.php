<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$wizard = new WpfdInstallWizard();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
$step      = isset($_GET['step']) ? sanitize_key($_GET['step']) : '';
$next_link = $wizard->getNextLink($step);
?>

<form method="post" id="quick-config-form">
    <?php wp_nonce_field('wpfd-setup-wizard', 'wizard_nonce'); ?>
    <input type="hidden" name="wpfd_save_step" value="1"/>
    <div class="wizard-header">
        <div class="title h1 font-size-35"><?php esc_html_e('Theme Selection', 'wpfd'); ?></div>
        <p class="description"><?php esc_html_e('We will guide you through the plugin main settings. You can also configure it later and skip the wizard', 'wpfd') ?></p>
    </div>
    <div class="wizard-content">
        <div class="ju-sub-heading h4 title">
            <?php esc_html_e('Select a default theme', 'wpfd'); ?>
        </div>
        <p class="description wpfd_width_100">
            <?php esc_html_e('Select a default to display your files ( can be changed later or select )', 'wpfd'); ?>
        </p>
        <div class="wizard-theme-config">
            <div class="wpfd-no-shadow wpfd-theme checked">
                <div class="overlay"></div>
                <p>Default theme</p>
                <i class="icon-theme-default"></i>
                <input type="checkbox" name="wizard-default-theme" checked="checked" />
            </div>
            <div class="wpfd-no-shadow wpfd-theme">
                <div class="overlay"></div>
                <p>Tree theme</p>
                <i class="icon-theme-tree"></i>
                <input type="checkbox" name="wizard-tree-theme" />
            </div>
            <div class="wpfd-no-shadow wpfd-theme">
                <div class="overlay"></div>
                <p>Table theme</p>
                <i class="icon-theme-table"></i>
                <input type="checkbox" name="wizard-table-theme" />
            </div>
            <div class="wpfd-no-shadow wpfd-theme">
                <div class="overlay"></div>
                <p>GGD theme</p>
                <i class="icon-theme-ggd"></i>
                <input type="checkbox" name="wizard-ggd-theme" />
            </div>
        </div>
    </div>

    <div class="wizard-footer">
        <div class="wpfd_row_full">
            <input type="submit" value="<?php esc_html_e('Continue', 'wpfd'); ?>" class="m-tb-20"
                   name="wpfd_save_step"/>
        </div>

        <a href="<?php echo esc_url(admin_url('options-general.php?page=option-folder'))?>" class="go-to-dash"><span><?php esc_html_e('I know what I\'m doing, skip wizard', 'wpfd'); ?></span></a>
    </div>
</form>
