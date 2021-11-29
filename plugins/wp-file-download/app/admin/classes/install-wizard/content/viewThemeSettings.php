<?php
/* Prohibit direct script loading */
defined('ABSPATH') || die('No direct script access allowed!');
$wizard = new WpfdInstallWizard();
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
$step      = isset($_GET['step']) ? sanitize_key($_GET['step']) : '';
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- View request, no action
$theme      = isset($_GET['theme']) ? sanitize_key($_GET['theme']) : '';
$next_link = $wizard->getNextLink($step);

if (!in_array($theme, array('ggd', 'tree', 'default', 'table'))) {
    die('<h1 class="title">Hey! Don\'t hack!</h1>');
}
/**
 * Generate theme param name
 *
 * @param string $theme Theme prefix name
 * @param string $name  Theme name
 *
 * @return void
 */
function wpfd_ce($theme, $name)
{
    if ($theme === 'default') {
        $attr = sanitize_key($name);
    } else {
        $attr = sanitize_key($theme . '_' . $name);
    }

    echo esc_attr('settings[' . $attr . ']');
}
?>

<form method="post" id="quick-config-form">
    <?php wp_nonce_field('wpfd-setup-wizard', 'wizard_nonce'); ?>
    <input type="hidden" name="wpfd_save_step" value="1"/>
    <input type="hidden" name="wpfd_theme" value="<?php echo esc_html($theme); ?>"/>
    <div class="wizard-header">
        <div class="title h1 font-size-35"><?php esc_html_e('Theme Settings', 'wpfd'); ?></div>
        <p class="description"><?php esc_html_e('We will guide you through the plugin main settings. You can also configure it later and skip the wizard', 'wpfd') ?></p>
    </div>

    <div class="ju-settings-options">
        <?php if (in_array($theme, array('ggd', 'default', 'table'))) : ?>
        <!-- only table, ggd, default theme -->
        <div class="ju-settings-option-group">
            <div class="ju-settings-option-item">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'marginleft'); ?>">Margin left</label>
                <input name="<?php wpfd_ce($theme, 'marginleft'); ?>" type="text" value="10" class="inputbox input-block-level ju-input" />
            </div>
            <div class="ju-settings-option-item">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'marginright'); ?>">Margin right</label>
                <input name="<?php wpfd_ce($theme, 'marginright'); ?>" type="text" value="10" class="inputbox input-block-level ju-input" />
            </div>
            <div class="ju-settings-option-item">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'margintop'); ?>">Margin top</label>
                <input name="<?php wpfd_ce($theme, 'margintop'); ?>" type="text" value="10" class="inputbox input-block-level ju-input" />
            </div>
            <div class="ju-settings-option-item">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'marginbottom'); ?>">Margin bottom</label>
                <input name="<?php wpfd_ce($theme, 'marginbottom'); ?>" type="text" value="10" class="inputbox input-block-level ju-input" />
            </div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
        </div>
        <?php endif; ?>

        <div class="ju-settings-option-group">
            <div class="ju-settings-option-item">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'croptitle'); ?>">Crop title</label>
                <input name="<?php wpfd_ce($theme, 'croptitle'); ?>" type="text" value="0" class="inputbox input-block-level ju-input" />
            </div>
            <div class="ju-settings-option-item">
                <label for="<?php wpfd_ce($theme, 'bgdownloadlink'); ?>" class="ju-setting-label">Background download link</label>
                <input autocomplete="off" type="text" class="ju-input minicolors minicolors-input" value="#76bc58" id="<?php wpfd_ce($theme, 'bgdownloadlink'); ?>" name="<?php wpfd_ce($theme, 'bgdownloadlink'); ?>" />
            </div>
            <div class="ju-settings-option-item">
                <label for="<?php wpfd_ce($theme, 'colordownloadlink'); ?>" class="ju-setting-label">Color download link</label>
                <input autocomplete="off" type="text" class="ju-input minicolors minicolors-input" value="#ffffff" id="<?php wpfd_ce($theme, 'colordownloadlink'); ?>" name="<?php wpfd_ce($theme, 'colordownloadlink'); ?>" />
            </div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
        </div>

        <div class="ju-settings-option-group">
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showtitle'); ?>">Show title</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showtitle'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showtitle'); ?>" name="<?php wpfd_ce($theme, 'showtitle'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showsize'); ?>">Show size</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showsize'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showsize'); ?>" name="<?php wpfd_ce($theme, 'showsize'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showhits'); ?>">Show hits</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showhits'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showhits'); ?>" name="<?php wpfd_ce($theme, 'showhits'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showversion'); ?>">Show version</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showversion'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showversion'); ?>" name="<?php wpfd_ce($theme, 'showversion'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showdownload'); ?>">Show download button</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showdownload'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showdownload'); ?>" name="<?php wpfd_ce($theme, 'showdownload'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showdateadd'); ?>">Show date added</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showdateadd'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showdateadd'); ?>" name="<?php wpfd_ce($theme, 'showdateadd'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showdatemodified'); ?>">Show date modified</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showdatemodified'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showdatemodified'); ?>" name="<?php wpfd_ce($theme, 'showdatemodified'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showsubcategories'); ?>">Show subcategories</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showsubcategories'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showsubcategories'); ?>" name="<?php wpfd_ce($theme, 'showsubcategories'); ?>" value="1">
                </div>
            </div>
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showdescription'); ?>">Show description</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showdescription'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showdescription'); ?>" name="<?php wpfd_ce($theme, 'showdescription'); ?>" value="1">
                </div>
            </div>
            <?php if (in_array($theme, array('ggd', 'default', 'table'))) : ?>
            <!-- only ggd, default, table theme -->
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showbreadcrumb'); ?>">Show breadcrumb</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showbreadcrumb'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showbreadcrumb'); ?>" name="<?php wpfd_ce($theme, 'showbreadcrumb'); ?>" value="1">
                </div>
            </div>
            <!-- only ggd, default, table theme -->
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'showfoldertree'); ?>">Show folder tree</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'showfoldertree'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'showfoldertree'); ?>" name="<?php wpfd_ce($theme, 'showfoldertree'); ?>" value="1">
                </div>
            </div>
            <?php elseif ($theme === 'tree') : ?>
            <?php endif; ?>
            <?php if ($theme === 'ggd' || $theme === 'tree') : ?>
            <!-- only ggd, tree theme -->
            <div class="ju-settings-option-item grid">
                <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'download_popup'); ?>">Download popup</label>
                <div class="ju-switch-button">
                    <label class="switch">
                        <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'download_popup'); ?>" value="1" checked="" class="inline ju-input">
                        <span class="slider"></span>
                    </label>
                    <input type="hidden" id="<?php wpfd_ce($theme, 'download_popup'); ?>" name="<?php wpfd_ce($theme, 'download_popup'); ?>" value="1">
                </div>
            </div>
            <?php endif; ?>
            <?php if ($theme === 'table') : ?>
                <!-- only table theme -->
                <div class="ju-settings-option-item grid">
                    <label class="ju-setting-label" for="<?php wpfd_ce($theme, 'stylingmenu'); ?>">Column display</label>
                    <div class="ju-switch-button">
                        <label class="switch">
                            <input type="checkbox" name="ref_<?php wpfd_ce($theme, 'stylingmenu'); ?>" value="1" checked="" class="inline ju-input">
                            <span class="slider"></span>
                        </label>
                        <input type="hidden" id="<?php wpfd_ce($theme, 'stylingmenu'); ?>" name="<?php wpfd_ce($theme, 'stylingmenu'); ?>" value="1">
                    </div>
                </div>
            <?php endif; ?>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
            <div class="ju-settings-option-item flexspan"></div>
        </div>

    </div>
    <div class="wizard-footer">
        <div class="wpfd_row_full">
            <input type="submit" value="<?php esc_html_e('Continue', 'wpfd'); ?>" class="m-tb-20" name="wpfd_save_step"/>
        </div>
    </div>
</form>
