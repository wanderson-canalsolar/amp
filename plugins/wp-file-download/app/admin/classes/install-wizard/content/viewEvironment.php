<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
?>
<form method="post">
    <?php wp_nonce_field('wpfd-setup-wizard', 'wizard_nonce'); ?>
    <div class="wizard-header">
        <div class="title h1"><?php esc_html_e('Environment Check', 'wpfd'); ?></div>
        <p class="description">
            <?php esc_html_e('We have checked your server environment. 
            If you see some warning below it means that some plugin features may not work properly.
            Reload the page to refresh the results', 'wpfd'); ?>
        </p>
    </div>
    <div class="wizard-content">
        <div class="version-container">
            <div class="label_text"><?php esc_html_e('PHP Version', 'wpfd'); ?></div>
            <div class="ju-settings-option wpfd_width_100">
                <div class="wpfd_row_full">
                    <label class="ju-setting-label php_version">
                        <?php esc_html_e('PHP ', 'wpfd'); ?>
                        <?php echo esc_html(PHP_VERSION) ?>
                        <?php esc_html_e('version', 'wpfd'); ?>
                    </label>

                    <div class="right-checkbox">
                        <?php if (version_compare(PHP_VERSION, '5.6', '<')) : ?>
                            <img src="<?php echo esc_url(WPFD_PLUGIN_URL . '/app/admin/assets/ui/images/icon-information.svg') ?>" class="img_warning" />
                        <?php else : ?>
                            <input type="checkbox" checked disabled class="filled-in media_checkbox"/>
                        <?php endif; ?>
                    </div>

                </div>
            </div>

            <?php if (version_compare(PHP_VERSION, '5.3', '<')) : ?>
                <p class="description text_left">
                    <?php esc_html_e('Your PHP version is ', 'wpfd'); ?>
                    <?php echo esc_html(PHP_VERSION) ?>
                    <?php esc_html_e('. For performance and security reasons it better to run PHP 7.2+. Comparing to previous versions the execution time of PHP 7.X is more than twice as fast and has 30 percent lower memory consumption', 'wpfd'); ?>
                </p>
            <?php else : ?>
                <p class="description">
                    <?php esc_html_e('Great ! Your PHP version is ', 'wpfd'); ?>
                    <?php echo esc_html(PHP_VERSION) ?>
                </p>
            <?php endif; ?>

        </div>

        <div class="other-container">
            <div class="label_text"><?php esc_html_e('PHP Extensions', 'wpfd'); ?></div>
            <div class="ju-settings-option wpfd_width_100">
                <div class="wpfd_row_full">
                    <label class="ju-setting-label"><?php esc_html_e('Curl', 'wpfd'); ?></label>
                    <div class="right-checkbox">
                        <?php if (!in_array('curl', get_loaded_extensions())) : ?>
                            <img src="<?php echo esc_url(WPFD_PLUGIN_URL . '/app/admin/assets/ui/images/icon-information.svg') ?>" class="img_warning" />
                        <?php else : ?>
                            <input type="checkbox" id="php_curl" name="php_curl" checked
                                   value="php_curl" disabled class="filled-in media_checkbox"/>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (!in_array('curl', get_loaded_extensions())) : ?>
                <p class="description text_left">
                    <?php esc_html_e('PHP Curl extension has not been detected. You need to activate in order to load video in media library and for all the cloud connections (like Google Drive, Dropbox...)', 'wpfd'); ?>
                </p>
            <?php endif; ?>

            <div class="ju-settings-option wpfd_width_100">
                <div class="wpfd_row_full">
                    <label class="ju-setting-label"><?php esc_html_e('GD Library', 'wpfd'); ?></label>
                    <div class="right-checkbox">
                        <?php if (function_exists('gd_info')) : ?>
                            <input type="checkbox" id="gd_info" name="gd_info" checked
                                   value="gd_info" disabled class="filled-in media_checkbox"/>
                        <?php else : ?>
                            <img src="<?php echo esc_url(WPFD_PLUGIN_URL . '/app/admin/assets/ui/images/icon-information.svg') ?>" class="img_warning" />
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <?php if (!function_exists('gd_info')) : ?>
                <p class="description text_left">
                    <?php esc_html_e('GD library is not detected. GD is an open source library related to image creation. The Watermark feature won’t work.', 'wpfd'); ?>
                </p>
            <?php endif; ?>

            <div class="ju-settings-option wpfd_width_100">
                <div class="wpfd_row_full">
                    <label class="ju-setting-label"><?php esc_html_e('Multibyte string', 'wpfd'); ?></label>
                    <div class="right-checkbox">
                        <?php if (!in_array('mbstring', get_loaded_extensions())) : ?>
                            <img src="<?php echo esc_url(WPFD_PLUGIN_URL . '/app/admin/assets/ui/images/icon-information.svg') ?>" class="img_warning" />
                        <?php else : ?>
                            <input type="checkbox" id="php_mbstring" name="php_mbstring" checked
                                   value="php_mbstring" disabled class="filled-in media_checkbox"/>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php if (!in_array('mbstring', get_loaded_extensions())) : ?>
                <p class="description text_left">
                    <?php esc_html_e('PHP Multibyte string extension has not been detected. You need to activate in order to use file search feature', 'wpfd'); ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
    <div class="wizard-footer">
        <input type="submit" value="<?php esc_html_e('Continue', 'wpfd'); ?>" class="" name="wpfd_save_step"/>
    </div>
</form>
