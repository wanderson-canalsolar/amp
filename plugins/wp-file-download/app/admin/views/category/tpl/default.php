<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

// No direct access.
defined('ABSPATH') || die();
global $wp_roles;

$options = array();
$wpRoles = $wp_roles->roles;
// Sort roles
uksort($wpRoles, function ($roleA, $roleB) {
    return strnatcmp($roleA, $roleB); // A-Z
});
foreach ($wpRoles as $k => $userRole) {
    $selected = '';
    if (in_array(strtolower($k), $this->category->roles)) {
        $selected = 'checked="checked"';
    }
    $options[] = '<label ref="wpfd_role_' . strtolower($k) . '"><input type="checkbox" name="params[roles][]" id="wpfd_role_' . strtolower($k) . '" value="' . strtolower($k) . '" ' . $selected . '/>' . esc_html($userRole['name']) . '</label>';
}

$ordering_options = array(
    'ordering'      => esc_html__('Ordering', 'wpfd'),
    'ext'           => esc_html__('Type', 'wpfd'),
    'title'         => esc_html__('Title', 'wpfd'),
    'description'   => esc_html__('Description', 'wpfd'),
    'size'          => esc_html__('Filesize', 'wpfd'),
    'created_time'  => esc_html__('Date added', 'wpfd'),
    'modified_time' => esc_html__('Date modified', 'wpfd'),
    'version'       => esc_html__('Version', 'wpfd'),
    'hits'          => esc_html__('Hits', 'wpfd'),
);

if ((int) $this->mainConfig['catparameters'] === 0) : ?>
    <style type="text/css">
        #category-theme-params {
            display: none;
        }
    </style>
<?php endif; ?>

<div class="wpfdparams">
    <form id="category_params">
        <?php
        /**
         * Action fire before category main settings field set in right panel
         *
         * @param integer Current category id
         */
        do_action('wpfd_before_fieldset_category_main_settings', $this->category->term_id);
        ?>
        <button class="ju-button orange-button category-submit" type="submit">
            <?php esc_html_e('Save Settings', 'wpfd'); ?>
        </button>
        <fieldset id="main-settings">
            <legend><?php esc_html_e('Main settings', 'wpfd'); ?></legend>
            <?php
            /**
             * Action fire before category main settings in right panel
             *
             * @param integer Current category id
             */
            do_action('wpfd_before_category_main_settings', $this->category->term_id);
            ?>
            <div class="control-group <?php echo ((int) $this->mainConfig['catparameters'] === 0) ? 'hidden' : ''; ?>">
                <label class="control-label" for="wpfd-theme"><?php esc_html_e('Theme', 'wpfd'); ?></label>
                <div class="controls">
                    <div class="wpfd-themes-select">
                        <?php
                        foreach ($this->themes as $theme) {
                            $checked = '';
                            if ($this->category->params['theme'] === $theme) {
                                $checked = 'checked';
                                $currentTheme = $theme;
                            }
                            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- it already escape
                            echo $this->themeNode($theme, $checked);
                        }
                        ?>
                        <div class="wpfd-theme-span"></div>
                        <input type="hidden" name="params[theme]" id="wpfd-theme" value="<?php echo esc_html($currentTheme); ?>" />
                    </div>
                </div>
            </div>
            <?php if (wpfd_can_edit_permission() || wpfd_user_is_owner_of_category($this->category)) : ?>
            <div class="control-group">
                <label class="control-label" for="visibility"><?php esc_html_e('Visibility', 'wpfd'); ?></label>
                <div class="controls">
                    <select name="params[visibility]" id="visibility" class="ju-input">
                        <option value="-1" <?php echo ($this->category->access === -1) ? 'selected="selected"' : ''; ?>>
                            <?php esc_html_e('Inherited', 'wpfd'); ?>
                        </option>
                        <option value="0" <?php echo ($this->category->access === 0) ? 'selected="selected"' : ''; ?>>
                            <?php esc_html_e('Public', 'wpfd'); ?>
                        </option>
                        <option value="1" <?php echo ($this->category->access === 1) ? 'selected="selected"' : ''; ?>>
                            <?php esc_html_e('Private', 'wpfd'); ?>
                        </option>
                    </select>
                </div>
                <div id="visibilitywrap" class="wpfd_roles_wrapper">

                        <?php
                        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- it already escape above
                        echo implode('', $options);
                        ?>


                </div>
            </div>
            <?php endif; ?>
            <?php if (defined('WPFDA_VERSION')) { ?>
                <div class="control-group">
                    <label class="control-label"
                           for="wpfd-social-locker"><?php esc_html_e('Lock content by socials', 'wpfd'); ?></label>
                    <div class="controls">
                        <select name="params[social]" id="wpfd-social-locker" class="ju-input">
                            <option value="0"
                                <?php echo (isset($this->category->params['social']) &&
                                (int) $this->category->params['social'] === 0) ? 'selected="selected"' : ''; ?>>
                                <?php esc_html_e('No', 'wpfd'); ?>
                            </option>
                            <option value="1"
                                <?php echo (isset($this->category->params['social']) &&
                                (int) $this->category->params['social'] === 1) ? 'selected="selected"' : ''; ?>>
                                <?php esc_html_e('Yes', 'wpfd'); ?></option>
                        </select>
                    </div>
                </div>
            <?php } ?>

            <div class="control-group">
                <label for="ordering" class="control-label"><?php esc_html_e('Ordering', 'wpfd'); ?></label>
                <div class="controls">
                    <select name="params[ordering]" id="ordering" class="ju-input">
                        <?php foreach ($ordering_options as $order_key => $order_text) { ?>
                            <option value="<?php echo esc_attr($order_key); ?>"
                                <?php selected($this->category->ordering, $order_key); ?>>
                                <?php echo esc_html($order_text); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="control-group">
                <label for="orderingdir" class="control-label"><?php esc_html_e('Ordering direction', 'wpfd'); ?></label>
                <div class="controls">
                    <select name="params[orderingdir]" id="orderingdir" class="ju-input">
                        <option value="asc" <?php selected($this->category->orderingdir, 'asc'); ?>>
                            <?php esc_html_e('Ascending', 'wpfd'); ?>
                        </option>
                        <option value="desc" <?php selected($this->category->orderingdir, 'desc'); ?>>
                            <?php esc_html_e('Descending', 'wpfd'); ?>
                        </option>
                    </select>
                </div>
            </div>
            <?php
            /**
             * Action fire after category main settings in right panel
             *
             * @param integer Current category id
             */
            do_action('wpfd_after_category_main_settings', $this->category->term_id);
            ?>
        </fieldset>
        <?php
        /**
         * Action fire after category main settings fieldset in right panel
         *
         * @param integer Current category id
         */
        do_action('wpfd_after_fieldset_category_main_settings', $this->category->term_id);
        ?>
        <?php if (wpfd_can_edit_permission()) : ?>
            <?php if (((int) $this->mainConfig['categoryown'] === 1) || ((int) $this->mainConfig['restrictfile'] === 1)) : ?>
            <fieldset id="permission-settings">
                <legend><?php esc_html_e('Permission settings', 'wpfd'); ?></legend>
            <?php endif; ?>
                <?php if ((int) $this->mainConfig['restrictfile'] === 1) { ?>
                    <div class="control-group">
                        <label title="" class="control-label"
                               for="category_canview_id"><?php esc_html_e('Single user access', 'wpfd'); ?></label>
                        <div class="controls">
                            <div class="field-user-wrapper">
                                <div class="input-append">
                                    <?php
                                    $user = get_userdata($this->category->params['canview']);
                                    $username = '';
                                    if ($user) {
                                        $username = $user->display_name;
                                    }
                                    ?>
                                    <input type="text" id="category_canview_select" value="<?php echo esc_attr($username); ?>"
                                           placeholder="<?php esc_html_e('Select a User', 'wpfd'); ?>" readonly=""
                                           class="field-user-category-access-name category ju-input">
                                    <?php
                                    $url_selectuser = 'admin.php?page=wpfd&amp;task=user.display&amp;noheader=true&amp;';
                                    $url_selectuser .= 'fieldtype=field-user-category-access&amp;cataction=true&amp;';
                                    $url_selectuser .= 'TB_iframe=true&amp;height=400&amp;width=800';
                                    ?>
                                    <a href="<?php echo esc_url(admin_url() . $url_selectuser); ?>"
                                       role="button" class="thickbox btn button-select" title="Select User">
                                        <span class="icon-user"></span></a>
                                    <a class="btn user-clear cat"><span class="icon-remove"></span></a>
                                </div>
                                <input type="hidden" id="category_canview_id" name="params[canview]" value="<?php echo
                                esc_attr($this->category->params['canview']); ?>" class="field-user-category-access category inputbox"
                                >
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if ((int) $this->mainConfig['categoryown'] === 1) { ?>
                    <div class="control-group">
                        <label title="" class="control-label"
                               for="category_own_id"><?php esc_html_e('User category owner', 'wpfd'); ?></label>
                        <div class="controls">
                            <div class="field-user-wrapper">
                                <div class="input-append">
                                    <?php

                                    $user2 = get_userdata($this->category->params['category_own']);
                                    $username = '';
                                    if ($user2) {
                                        $username = $user2->display_name;
                                    }
                                    ?>
                                    <input type="text" id="category_category-own_select" value="<?php echo esc_attr($username); ?>"
                                           placeholder="<?php esc_html_e('Select a User', 'wpfd'); ?>" readonly=""
                                           class="field-user-category-own-name ju-input">
                                    <?php
                                    $url_selectuser = 'admin.php?page=wpfd&amp;task=user.display&amp;noheader=true&amp;';
                                    $url_selectuser .= 'fieldtype=field-user-category-own&amp;cataction=true&amp;';
                                    $url_selectuser .= 'TB_iframe=true&amp;height=400&amp;width=800';
                                    ?>
                                    <a href="<?php echo esc_url(admin_url() . $url_selectuser); ?>"
                                       role="button" class="thickbox btn button-select" title="Select User">
                                        <span class="icon-user"></span>
                                    </a>
                                    <a class="btn user-clear-category"><span class="icon-remove"></span></a>
                                </div>
                                <input type="hidden" id="category_own_id" name="params[category_own]"
                                       value="<?php echo esc_attr($this->category->params['category_own']); ?>"
                                       class="field-user-category-own inputbox">
                                <input type="hidden" id="category_own_id_old" name="params[category_own_old]"
                                       value="<?php echo esc_attr($this->category->params['category_own']); ?>">
                            </div>
                        </div>
                    </div>
                <?php } ?>
                <?php if (((int) $this->mainConfig['categoryown'] === 1) || ((int) $this->mainConfig['restrictfile'] === 1)) : ?>
            </fieldset>
                <?php endif; ?>
        <?php endif; ?>
        <div id="category-theme-params">
            <?php
            if (WpfdBase::checkExistTheme($this->category->params['theme'])) {
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
                echo $this->loadTemplate('theme-' . $this->category->params['theme']);
            } else {
                $dir = trailingslashit(realpath(dirname(wpfd_locate_theme($this->category->params['theme'], 'theme.php'))));
                $this->setPath($dir);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print output html
                echo $this->loadTemplate('theme-' . $this->category->params['theme']);
            }
            ?>
        </div>
        <?php if ((int) $this->mainConfig['shortcodecat'] === 1) { ?>
            <fieldset id="category-shortcode">
                <legend><?php esc_html_e('Category shortcode', 'wpfd'); ?></legend>
                <div class="control-group">
                    <div class="controls" style="width:100%">
                        <input type="text" id="shortcodecat" name="shortcodecat" readonly="true"
                               value='[wpfd_category id="<?php echo esc_attr($this->category->term_id); ?>"]'
                               class="ju-input"
                               onclick="jQuery(this).select();document.execCommand('copy');jQuery.gritter.add({text: wpfd_admin.msg_shortcode_copied_to_clipboard});">
                    </div>
                </div>
                <div class="control-group">
                    <small>
                        <?php esc_html_e('Usage: Click to copy this shortcode then paste to where you want to display this category.', 'wpfd'); ?>
                    </small>
                </div>
            </fieldset>
        <?php } ?>
        <?php
        /**
         * Action fire before category save button in right panel
         *
         * @param integer Current category id
         */
        do_action('wpfd_save_category_settings_button', $this->category->term_id);
        ?>
        <button class="ju-button orange-button category-submit" type="submit">
            <?php esc_html_e('Save Settings', 'wpfd'); ?>
        </button>
    </form>
</div>
