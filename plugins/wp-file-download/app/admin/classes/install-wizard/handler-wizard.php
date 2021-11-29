<?php
if (!defined('ABSPATH')) {
    exit;
}


/**
 * Class WpfdHandlerWizard
 */
class WpfdHandlerWizard
{
    /**
     * WpfdHandlerWizard constructor.
     */
    public function __construct()
    {
    }

    /**
     * Save Environment handle
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveEvironment($current_step)
    {
        check_admin_referer('wpfd-setup-wizard', 'wizard_nonce');
        /*
         * Do no thing
         */
        $wizard = new WpfdInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getNextLink($current_step)));
        exit;
    }

    /**
     * Save theme config
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveThemeConfig($current_step)
    {
        check_admin_referer('wpfd-setup-wizard', 'wizard_nonce');

        WP_Filesystem();

        if (array_key_exists('wizard-default-theme', $_POST) && $_POST['wizard-default-theme'] === 'on') {
            $theme = 'default';
        } elseif (array_key_exists('wizard-ggd-theme', $_POST) && $_POST['wizard-ggd-theme'] === 'on') {
            $theme = 'ggd';
        } elseif (array_key_exists('wizard-tree-theme', $_POST) && $_POST['wizard-tree-theme'] === 'on') {
            $theme = 'tree';
        } elseif (array_key_exists('wizard-table-theme', $_POST) && $_POST['wizard-table-theme'] === 'on') {
            $theme = 'table';
        } else {
            $theme = 'default';
        }
        $options = array(
            'catparameters' => 0,
            'defaultthemepercategory' => $theme
        );

        $config = get_option('_wpfd_global_config');
        foreach ($options as $key => $value) {
            $config[$key] = $value;
        }
        update_option('_wpfd_global_config', $config);

        $wizard = new WpfdInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getThemeConfigLink($current_step, $theme)));
        exit;
    }

    /**
     * Save Theme Settings
     *
     * @param string $current_step Current step
     *
     * @return void
     */
    public static function saveThemeSettings($current_step)
    {
        check_admin_referer('wpfd-setup-wizard', 'wizard_nonce');

        WP_Filesystem();
        $theme = array_key_exists('wpfd_theme', $_POST) ? sanitize_key($_POST['wpfd_theme']) : 'default';
        $settings = array_key_exists('settings', $_POST) ? $_POST['settings'] : array();

        $default_config = '{"marginleft":"10","marginright":"10", "margintop":"10", "marginbottom":"10",';
        $default_config .= '"showsize":"1","showtitle":"1","croptitle":"0","showdescription":"1","showversion":"1",';
        $default_config .= '"showhits":"1","showdownload":"1","bgdownloadlink":"#76bc58",';
        $default_config .= '"colordownloadlink":"#ffffff","showdateadd":"1","showdatemodified":"0",';
        $default_config .= '"showsubcategories":"1","showcategorytitle":"1","showbreadcrumb":"1","showfoldertree":"0"}';

        $ggd_config = '{"ggd_marginleft":"10","ggd_marginright":"10", "ggd_margintop":"10", "ggd_marginbottom":"10",';
        $ggd_config .= '"ggd_croptitle":"0", "ggd_showsize":"1","ggd_showtitle":"1","ggd_showdescription":"1",';
        $ggd_config .= '"ggd_showversion":"1","ggd_showhits":"1","ggd_showdownload":"1",';
        $ggd_config .= '"ggd_bgdownloadlink":"#76bc58","ggd_colordownloadlink":"#ffffff","ggd_showdateadd":"1",';
        $ggd_config .= '"ggd_showdatemodified":"0","ggd_showsubcategories":"1","ggd_showcategorytitle":"1",';
        $ggd_config .= '"ggd_showbreadcrumb":"1","ggd_showfoldertree":"0","ggd_download_popup":"1"}';

        $table_config = '{"table_stylingmenu":"1", "table_showsize":"1", "table_showtitle":"1",';
        $table_config .= '"table_showdescription":"1", "table_showversion":"1", "table_showhits":"1",';
        $table_config .= '"table_croptitle":"0", "table_showdownload":"1", "table_bgdownloadlink":"#76bc58",';
        $table_config .= '"table_colordownloadlink":"#ffffff", "table_showdateadd":"1", "table_showdatemodified":"0",';
        $table_config .= '"table_showsubcategories":"1", "table_showcategorytitle":"1",';
        $table_config .= '"table_showbreadcrumb":"1", "table_showfoldertree":"0"}';

        $tree_config    = '{"tree_showsize":"1","tree_croptitle":"0",';
        $tree_config    .= '"tree_showtitle":"1","tree_showdescription":"1","tree_showversion":"1","tree_showhits":"1",';
        $tree_config    .= '"tree_showdownload":"1","tree_bgdownloadlink":"#76bc58","tree_colordownloadlink":"#ffffff",';
        $tree_config    .= '"tree_showdateadd":"1","tree_showdatemodified":"0","tree_showsubcategories":"1",';
        $tree_config    .= '"tree_showcategorytitle":"1","tree_download_popup":"1"}';

        $default        = array(
            'default' => $default_config,
            'ggd'     => $ggd_config,
            'table'   => $table_config,
            'tree'    => $tree_config,
        );

        if (is_array($settings) && !empty($settings)) {
            update_option('_wpfd_' . $theme . '_config', $settings);
        } else {
            update_option('_wpfd_' . $theme . '_config', $default[$theme]);
        }

        $wizard = new WpfdInstallWizard();
        wp_safe_redirect(esc_url_raw($wizard->getNextLink($current_step)));
        exit;
    }
}
