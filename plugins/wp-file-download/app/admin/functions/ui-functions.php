<?php
/*
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version: 4.4
 */

defined('ABSPATH') || die();

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

/**
 * CONFIGURATION MENU
 */
add_action('wpfd_admin_ui_configuration_menu', 'wpfd_admin_ui_menu_logo', 10);
add_action('wpfd_admin_ui_configuration_menu', 'wpfd_admin_ui_menu_search', 20);
add_action('wpfd_admin_ui_configuration_menu', 'wpfd_admin_ui_configuration_menu_items', 30);

/**
 * Display JoomUnited logo in left menu
 *
 * @return void
 */
function wpfd_admin_ui_menu_logo()
{
    $logo = plugins_url('../assets/ui/images/logo-joomUnited-white.png', __FILE__);
    ?>
    <div class="ju-logo">
        <a href="https://www.joomunited.com" target="_blank" title="Visit plugin site">
            <img src="<?php echo esc_url($logo); ?>" alt="WP File Download" />
        </a>
    </div>
    <?php
}

/**
 * Display JoomUnited Search box in left menu
 *
 * @return void
 */
function wpfd_admin_ui_menu_search()
{
    ?>
    <div class="ju-menu-search">
        <i class="material-icons ju-menu-search-icon">search</i>
        <input type="text" class="ju-menu-search-input" placeholder="Search settings" />
    </div>
    <?php
}

/**
 * Print menu items
 *
 * @return void
 */
function wpfd_admin_ui_configuration_menu_items()
{
    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this need to print out menu struct
    echo wpfd_admin_ui_build_menu_html();
}

/**
 * Menu items
 *
 * @return array
 */
function wpfd_admin_ui_configuration_menu_get_items()
{
    $items = array(
        'main-settings'      => array(esc_html__('Main setting', 'wpfd'), 'configform', 10),
        'search-upload'      => array(esc_html__('Search setting', 'wpfd'), 'searchform', 20),
        'themes'             => array(esc_html__('Themes', 'wpfd'), 'themeforms', 30),
        'clone-theme'        => array(esc_html__('Clone theme', 'wpfd'), 'clone_form', 40),
        'shortcodes'         => array(esc_html__('Shortcodes', 'wpfd'), 'file_catform,upload_form,search_shortcode', 50),
        'export'             => array(esc_html__('Import/Export', 'wpfd'), 'exportform', 51),
        'translate'          => array(esc_html__('Translate', 'wpfd'), 'translate_form', 60),
        'email-notification' => array(esc_html__('Email notification', 'wpfd'), 'notifications_form', 70),
        'user-roles'         => array(esc_html__('User roles', 'wpfd'), 'rolesform', 80),
    );
    $items = apply_filters('wpfd_admin_ui_configuration_menu_get_items', $items);

    // Sort menu by position
    uasort($items, function ($a, $b) {
        return $a[2] - $b[2];
    });
    return $items;
}

/**
 * PAGES
 */
/**
 * Configuration User Role Page
 *
 * @return string
 */
function wpfd_admin_ui_user_roles_content()
{
    $html = '<h2 class="ju-heading">' . esc_html__('User Roles', 'wpfd') . '</h2>';
    $html .= '<form id="wpfd-role-form" method="post" action="admin.php?page=wpfd-config&amp;task=config.saveroles">';
    $html .= wp_nonce_field('wpfd_role_settings', 'wpfd_role_nonce', true, false);
    $html .= wpfd_admin_ui_user_roles_search();
    $html .= wpfd_admin_ui_user_roles_role_cap_fields();
    $html .= wpfd_admin_ui_button('Save', 'orange-button');
    $html .= '</form>';

    return $html;
}

/**
 * Get global roles
 *
 * @return WP_Roles
 */
function wpfd_admin_ui_user_roles_get_roles()
{
    global $wp_roles;

    if (!isset($wp_roles)) {
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- create if wp_roles is null
        $wp_roles = new WP_Roles();
    }

    return $wp_roles;
}

/**
 * Role search bar
 *
 * @return string
 */
function wpfd_admin_ui_user_roles_search()
{
    ob_start();
    ?>
    <div class="ju-role-search">
        <i class="material-icons ju-role-search-icon">search</i>
        <input type="text" class="ju-role-search-input" placeholder="Search role name" />
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
}

/**
 * Build roles fields
 *
 * @return string
 */
function wpfd_admin_ui_user_roles_role_cap_fields()
{
    Application::getInstance('Wpfd');
    $configModel        = Model::getInstance('config');
    $output             = '';
    $c_roles            = wpfd_admin_ui_user_roles_get_roles();
    $roles              = $c_roles->role_objects;
    $roles_name         = $c_roles->role_names;
    $glb_config         = get_option('_wpfd_global_config');
    $default_config     = $configModel->getConfig();
    $guest_roles        = new stdClass();
    $guest_roles->name  = 'guest';
    $guest_caps         = array('wpfd_download_files' => esc_html__('Download files', 'wpfd') , 'wpfd_preview_files' => esc_html__('Preview files', 'wpfd'));
    if (!isset($glb_config['guest_download_files'])) {
        if (isset($default_config['guest_download_files'])) {
            $glb_config['guest_download_files'] = $default_config['guest_download_files'];
        } else {
            $glb_config['guest_download_files'] = 1;
        }
        update_option('_wpfd_global_config', $glb_config);
    }
    if (!isset($glb_config['guest_preview_files'])) {
        if (isset($default_config['guest_preview_files'])) {
            $glb_config['guest_preview_files'] = $default_config['guest_preview_files'];
        } else {
            $glb_config['guest_preview_files'] = 1;
        }
        update_option('_wpfd_global_config', $glb_config);
    }
    if (isset($glb_config['guest_download_files']) && (int)$glb_config['guest_download_files'] === 1) {
        $guest_roles->capabilities['wpfd_download_files'] = true;
    } else {
        if (isset($guest_roles->capabilities['wpfd_download_files'])) {
            unset($guest_roles->capabilities['wpfd_download_files']);
        }
    }

    if (isset($glb_config['guest_preview_files']) && (int)$glb_config['guest_preview_files'] === 1) {
        $guest_roles->capabilities['wpfd_preview_files'] = true;
    } else {
        if (isset($guest_roles->capabilities['wpfd_preview_files'])) {
            unset($guest_roles->capabilities['wpfd_preview_files']);
        }
    }

    // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
    if (is_countable($roles) && !empty($roles)) {
        foreach ($roles as $name => $role) {
            $readableName = $roles_name[$role->name];
            $output .= '<h3 class="ju-heading ju-toggle">' . $readableName . '</h3>';
            $caps = wpfd_admin_ui_user_roles_filter_default_cap();
            $output .= '<div class="ju-settings-option-group">';
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($roles) && !empty($roles)) {
                foreach ($caps as $post_key => $post_cap) {
                    $output .= wpfd_admin_ui_user_roles_role_cap_field($role, $post_key, $post_cap);
                }
            }
            $output .= '</div>';
        }
    }

    if (isset($guest_roles)) {
        $output .= '<h3 class="ju-heading ju-toggle">' . esc_html__('Guest', 'wpfd') . '</h3>';
        $output .= '<div class="ju-settings-option-group">';

        foreach ($guest_caps as $guest_post_key => $guest_post_cap) {
            $output .= wpfd_admin_ui_user_roles_role_cap_field($guest_roles, $guest_post_key, $guest_post_cap);
        }

        $output .= '</div>';
    }

    return $output;
}

/**
 * Build user role field
 *
 * @param object $role     Role
 * @param string $post_key Key
 * @param string $post_cap Caption
 *
 * @return false|string
 */
function wpfd_admin_ui_user_roles_role_cap_field($role, $post_key, $post_cap)
{
    $name = $role->name . '[' . $post_key . ']';
    $id = 'wpfd-' . $role->name . '-' . $post_key . '-edit';
    $checked = isset($role->capabilities[$post_key]);

    $globalConfig = get_option('_wpfd_global_config');
    if ((!isset($role->capabilities[$post_key]) && $post_key === 'wpfd_download_files'
        && $role->name !== 'guest' && !isset($globalConfig[$role->name . '_download_files']))
    || (!isset($role->capabilities[$post_key]) && $post_key === 'wpfd_preview_files'
            && $role->name !== 'guest' && !isset($globalConfig[$role->name . '_preview_files']))) {
        $checked = true;
    }
    $tooltips = array(
        'wpfd_create_category' => esc_html__('Allow users in this user role to create categories, including cloud categories', 'wpfd'),
        'wpfd_edit_category' => esc_html__('Allow users in this user role to edit all the file categories settings', 'wpfd'),
        'wpfd_edit_own_category' => esc_html__('Allow users in this user role to edit ONLY their OWN file categories (need to be owner)', 'wpfd'),
        'wpfd_delete_category' => esc_html__('Allow users in this user role to delete file categories', 'wpfd'),
        'wpfd_manage_file' => esc_html__('Allow users in this user role to access to the file management, restrictions above still applies!', 'wpfd'),
        'wpfd_edit_permission' => esc_html__('Allow users in this user role to edit category/file permission!', 'wpfd'),
        'wpfd_download_files' => esc_html__('Allow users in this user role to download files', 'wpfd'),
        'wpfd_preview_files' => esc_html__('Allow users in this user role to preview files', 'wpfd'),
    );
    return wpfd_admin_ui_switcher($name, $id, $post_cap, $checked, $tooltips[$post_key]);
}

/**
 * Filter remove default wordpress cap
 *
 * @return array
 */
function wpfd_admin_ui_user_roles_filter_default_cap()
{
    $fileType       = get_post_type_object('wpfd_file');
    $post_type_caps = $fileType->cap;

    $caps            = (array) $post_type_caps;
    $wp_default_caps = array(
        'read',
        'read_post',
        'read_private_posts',
        'create_posts',
        'edit_posts',
        'edit_post',
        'edit_others_posts',
        'delete_post',
        'delete_posts',
        'publish_posts'
    );
    foreach ($wp_default_caps as $default_cap) {
        unset($caps[$default_cap]);
    }

    return $caps;
}

/**
 * Configuration Export Page
 *
 * @return string
 */
function wpfd_admin_ui_export_content()
{
    $html = '<h2 class="ju-heading">' . esc_html__('Import/Export', 'wpfd') . '</h2>';
    $html .= '<form id="wpfd-import-export-form" method="post" action="admin.php?page=wpfd-config&amp;task=config.savefolderimportexportparams" enctype="multipart/form-data">';
    $html .= wp_nonce_field('wpfd_export_settings', 'wpfd_export_nonce', true, false);
    $html .= wpfd_admin_ui_export_fields();
    $html .= wpfd_admin_ui_import_server_folders();
    $html .= wpfd_admin_ui_import_download_manager();
//    $html .= wpfd_admin_ui_button('Save', 'orange-button');
    $html .= '<input type="submit" name="wpfd-import-export-save" value="Save" class="ju-button orange-button">';
    $html .= '</form>';

    return $html;
}


/**
 * Build export fields
 *
 * @return string
 */
function wpfd_admin_ui_export_fields()
{

    $config             = get_option('_wpfd_global_config');
    $export_folder_type = ( isset($config['export_folder_type']) ) ? $config['export_folder_type'] :  'only_folder';
    $import_file_params = ( isset($config['import_file_params']) ) ? $config['import_file_params'] :  array();
    $xml_category_disc  = ( isset($config['import_xml_disc']) ) ? $config['import_xml_disc'] : '';
    $bytes              = apply_filters('import_upload_size_limit', wp_max_upload_size());
    $size               = size_format($bytes);
    ob_start();
    ?>
    <p class="import_export_desc description"><?php echo esc_html__('Import and Export WP File Download files and categories from one server to another', 'wpfd'); ?></p>
    <div class="ju-settings-option full-width">
        <label title="<?php echo esc_html__('Select what do you want to export and run to generate a file that you will import on another website', 'wpfd'); ?>" for="export_folder_type" class="ju-setting-label"><?php echo esc_html__('Export Files/Categories', 'wpfd'); ?></label>
        <select name="export_folder_type" id="export_folder_type" class="inputbox input-block-level ju-input">
            <option value="all" <?php selected($export_folder_type, 'all'); ?> style="width: 100%; max-width: 100%; box-sizing: border-box"><?php echo esc_html__('All categories and files', 'wpfd'); ?></option>
            <option value="only_folder" <?php selected($export_folder_type, 'only_folder'); ?> style="width: 100%; max-width: 100%; box-sizing: border-box"><?php echo esc_html__('Only the category structure', 'wpfd'); ?></option>
            <option value="selection_folder" <?php selected($export_folder_type, 'selection_folder'); ?> style="width: 100%; max-width: 100%; box-sizing: border-box"><?php echo esc_html__('A selection of categories and files', 'wpfd'); ?></option>
        </select>
        <input type="hidden" name="wpfd_export_folders" class="wpfd_export_folders">
        <a href="#" id="open_export_tree_folders_btn" class="ju-button no-background open_export_tree_folders hide"><?php echo esc_html__('Select categories', 'wpfd'); ?></a>
        <a href="#" id="wpfd-run-export" class="ju-button orange-outline-button wpfd-run-export"><span class="spinner" style="display:none; margin: 0; vertical-align: middle"></span><?php echo esc_html__('Run export', 'wpfd'); ?></a>
    </div>
    <div class="ju-settings-option full-width">
        <div class="ju-settings-option-item">
            <label title="<?php echo esc_html__('Browse and select the file you\'ve previously exported to run the files & categories import', 'wpfd'); ?>" class="ju-setting-label"><?php echo esc_html__('Import Files/Categories', 'wpfd'); ?></label>
            <input type="file" name="import" id="wpfd_import_folders" class="wpfd_import_folders">
            <input type="hidden" name="max_file_size" value="<?php echo esc_attr($bytes); ?>"/>
            <button name="wpfd_import_folders_btn" type="submit" id="wpfd_import_folder_btn" class="ju-button wpfd_import_folder_btn orange-outline-button waves-effect waves-light"
                    data-path="<?php echo ( isset($import_file_params['path']) ) ? esc_attr($import_file_params['path']) : '' ?>"
                    data-id="<?php echo ( isset($import_file_params['id']) ) ? esc_attr($import_file_params['id']) : '' ?>"
                    data-import_only_folder="<?php echo (isset($import_file_params['import_only_folder'])) ? esc_attr($import_file_params['import_only_folder']) : 1 ?>">
                <?php esc_html_e('Run import', 'wpfd'); ?>
            </button>
        </div>
        <div class="ju-settings-option-item">
            <label class="wpfdqtip" data-alt="<?php esc_html_e('Server values are upload_max_filesize and post_max_size', 'wpfd'); ?>">
                <?php printf(esc_html__('Maximum size, server value: %s', 'wpfd'), esc_html($size)); ?>
            </label>

            <?php if (apply_filters('import_allow_import_only_folder', true)) : ?>
                <p class="only-folder-option">
                    <input type="checkbox" value="1" name="import_only_folder" id="import-attachments" checked/>
                    <label for="import-attachments"><?php esc_html_e('Import only category structure (not files)', 'wpfd'); ?></label>
                </p>
            <?php endif; ?>
            <input type="hidden" name="wpfd-import-xml-disc" id="wpfd-import-xml-disc" value="<?php echo esc_attr($xml_category_disc) ?>" />
            <div class="wpfd_import_error_message_wrap"></div>
        </div>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}


/**
 * Build import server folders
 *
 * @return string
 */
function wpfd_admin_ui_import_server_folders()
{
    if (wpfd_can_edit_category() || wpfd_can_edit_own_category()) {
        Application::getInstance('Wpfd');
        $config         = get_option('_wpfd_global_config');
        $configModel    = Model::getInstance('config');
        $defaultConfig  = $configModel->getConfig();
        $allowed_ext    = (isset($config['allowedext'])) ? $config['allowedext'] : $defaultConfig['allowedext'];
        ob_start();
        ?>

        <h2 class="ju-heading"><?php echo esc_html__('Import server folders', 'wpfd'); ?></h2>
        <div class="ju-settings-option full-width" id="import-server-folders">
            <p class="description"><?php esc_html_e('Import local server files and folders into WP File Download', 'wpfd'); ?></p>
            <span class="text-orange" style="word-break: break-all;"><?php echo esc_html($allowed_ext); ?></span>
            <div class="wpfd_row_full">
                <div id="wpfd_foldertree" class="wpfd-no-padding"></div>
                <div class="wpfd-process-bar-full process_import_ftp_full" style="">
                    <div class="wpfd-process-bar process_import_ftp" data-w="0"></div>
                </div>
                <button type="button" id="import-server-folders-btn"
                        class="ju-button no-background orange-outline-button waves-effect waves-light">
                    <label style="line-height: 20px"><?php esc_html_e('Import Folder', 'wpfd'); ?></label>
                    <span class="spinner" style="display:none; margin: 0; vertical-align: middle"></span>
                </button>
                <span class="wpfd_info_import"><?php esc_html_e('Imported!', 'wpfd'); ?></span>
            </div>
        </div>

        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

/**
 * Build import folders from download manager
 *
 * @return string
 */
function wpfd_admin_ui_import_download_manager()
{
    // Check if active Wp Download Manager
    $pluginWpdm = 'download-manager/download-manager.php';
    $pluginList = get_option('active_plugins');

    if (in_array($pluginWpdm, $pluginList)) {
        global $wpdb;
        $args = array(
            'taxonomy'      => 'wpdmcategory',
            'orderby'       => 'term_group',
            'hierarchical'  => true,
            'hide_empty'    => 0,
            'parent'        => 0
        );
        $wpdmCategory = get_terms($args);
        ob_start(); ?>
        <h2 class="ju-heading"><?php echo esc_html__('WP Download Manager Import', 'wpfd'); ?></h2>
        <div class="ju-settings-option full-width" id="import-folders-download-manager">
            <div class="name-section">
                <p class="wpdm-desc"><?php echo esc_html__('Import files and categories from the 3rd party plugin WP Download Manager', 'wpfd'); ?></p>
            </div>
            <div class="select-file-section">
                <label class="ju-setting-label"><?php echo esc_html__('Root category for import', 'wpfd'); ?></label>
                <select class="inputbox input-block-level ju-input choose-category">
                    <option value=""><?php echo esc_html__('Select a category', 'wpfd'); ?></option>
                    <?php
                    if (!empty($wpdmCategory)) {
                        $results    = array();
                        $cleanList  = array();
                        echo '<option value="all">'. esc_html('All category', 'wpfd') .'</option>';
                        foreach ($wpdmCategory as $category) {
                            $category->level    = 0;
                            $cleanList[]        = $category;
                            $list               = wpfd_admin_import_download_manager_category_level($category, $results);
                            if (!empty($list)) {
                                foreach ($list as $term) {
                                    $cleanList[] = $term;
                                }
                            }
                        }

                        if (!empty($cleanList)) {
                            foreach ($cleanList as $cate) {
                                $level = '';
                                if (isset($cate->level) && (int)$cate->level > 0) {
                                    for ($i = 0; $i < (int)$cate->level; $i++) {
                                        $level .= '-';
                                    }
                                }
                                echo '<option value="'. esc_attr($cate->term_id) .'">'. esc_attr($level) . esc_attr($cate->name) .'</option>';
                            }
                        }
                    }
                    ?>
                </select>
                <button type="button" id="run-import-download-manager-btn" class="ju-button orange-outline-button">
                    <?php echo esc_html__('Run Import', 'wpfd'); ?>
                    <span class="spinner" style="display:none; margin: 0; vertical-align: middle"></span>
                </button>
            </div>
            <div id="wpdm-import-message" class="message-section" style="display: none; visibility: hidden"></div>
        </div>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
}

/**
 * Get level of a parent category
 *
 * @param object|mixed $rootCategory Root category
 * @param array        $results      Result list
 *
 * @return array
 */
function wpfd_admin_import_download_manager_category_level($rootCategory, $results)
{
    if (!is_array($results)) {
        $results = array();
    }
    $categories = get_terms('wpdmcategory', 'orderby=term_group&hierarchical=1&hide_empty=0&parent='. $rootCategory->term_id);
    if ($categories) {
        foreach ($categories as $cat) {
            $cat->level = $rootCategory->level + 1;
            $results[]  = $cat;
            $results    = wpfd_admin_import_download_manager_category_level($cat, $results);
        }
    }

    return $results;
}


/**
 * HELPERS
 */
/**
 * Switcher
 *
 * @param string  $name    Input name
 * @param string  $id      Input id
 * @param string  $label   Input label
 * @param boolean $checked Input checked
 * @param string  $tooltip Tooltip
 *
 * @return false|string
 */
function wpfd_admin_ui_switcher($name = '', $id = '', $label = '', $checked = false, $tooltip = '')
{
    ob_start();
    ?>
    <div class="ju-settings-option">
        <label title="<?php echo esc_html($tooltip); ?>" for="<?php echo esc_attr($id); ?>" class="ju-setting-label"><?php echo esc_html($label); ?></label>
        <div class="ju-switch-button">
            <label class="switch">
                <input type="checkbox" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($id); ?>" <?php checked($checked, 1); ?> />
                <span class="slider"></span>
            </label>
        </div>
    </div>
    <?php
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

/**
 * Ju button element
 *
 * @param string $label Button label
 * @param string $class Additional button class
 *
 * @return string
 */
function wpfd_admin_ui_button($label = 'Save', $class = '')
{
    return '<input type="submit" value="' . $label . '" class="ju-button ' . $class . '">';
}

/**
 * Build left menu html
 *
 * @param null|array $items Menu items
 *
 * @return string
 */
function wpfd_admin_ui_build_menu_html($items = null)
{
    if (is_null($items)) {
        $items = wpfd_admin_ui_configuration_menu_get_items();
    }
    $html = '<ul class="tabs ju-menu-tabs">';
    foreach ($items as $key => $item) {
        $html .= '<li class="tab">';
        $html .= '<a href="#wpfd-' . $key . '" class="link-tab waves-effect waves-light ' . $key . '">';

        if (wpfd_admin_ui_icon_exists($key)) {
            $icon = plugins_url('app/admin/assets/ui/images/icon-' . $key . '.svg', WPFD_PLUGIN_FILE);
            $html    .= '<img src="' . $icon . '" />&nbsp;';
        } elseif (isset($item[3])) {
            $html    .= '<img src="' . esc_url($item[3]) . '" />&nbsp;';
        }
        $html .= $item[0];
        $html .= '</a>';
        $html .= '</li>';
    }
    $html .= '</ul>';

    return $html;
}

/**
 * Check for icon is exists
 *
 * @param string $name Icon name
 *
 * @return boolean
 */
function wpfd_admin_ui_icon_exists($name)
{
    $iconPath = realpath(dirname(WPFD_PLUGIN_FILE)) . DIRECTORY_SEPARATOR;
    $iconPath .= 'app' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR;
    $iconPath .= 'ui' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'icon-' . esc_attr($name) . '.svg';

    if (file_exists($iconPath)) {
        return true;
    }
    return false;
}

/**
 * Build content wrapper
 *
 * @param string $name    Wrapper name
 * @param string $html    Content html
 * @param string $message Message
 *
 * @return string
 */
function wpfd_admin_ui_configuration_build_content($name, $html, $message = '')
{
    $output = '<div class="ju-content-wrapper" id="wpfd-' . esc_attr($name) . '">';
    if ($message !== '') {
        $output .= $message;
    }
    $output .= $html;
    $output .= '</div>';

    return $output;
}

/**
 * Build top bar tabs
 *
 * @param array  $tabs    Tabs array
 * @param string $message Message
 *
 * @return string
 */
function wpfd_admin_ui_configuration_build_tabs($tabs, $message = '')
{
    if (is_array($tabs) && !empty($tabs)) {
        $tabHtml = '<div class="ju-top-tabs-wrapper"><ul class="tabs ju-top-tabs">';
        $html = '';

        foreach ($tabs as $key => $content) {
            $tabHtml .= '<li class="tab">';
            if (is_array($content)) {
                $tabTitle = isset($content['title']) ? $content['title'] : wpfd_admin_ui_configuration_parse_tab_name_from_key($key);
                $tabContent = isset($content['content']) ? $content['content'] : '';
            } else {
                $tabTitle = wpfd_admin_ui_configuration_parse_tab_name_from_key($key);
                $tabContent = $content;
            }
            $tabHtml .= '<a href="#' . $key . '" class="link-tab">' . $tabTitle . '</a>';
            $tabHtml .= '</li>';
            $html .= '<div class="ju-content-wrapper" id="' . $key . '">';
            if ($message !== '') {
                $html .= $message;
            }
            $html .= $tabContent;
            $html .= '</div>';
        }
        $tabHtml .= '</div>';

        return $tabHtml . $html;
    }

    return '';
}

/**
 * Get tab name from tab key
 *
 * @param string $key Key name
 *
 * @return string
 */
function wpfd_admin_ui_configuration_parse_tab_name_from_key($key)
{
    $key = preg_replace('/\_/', ' ', $key);

    // Made ggd theme name upper in tab name
    if (strtolower($key) === 'ggd') {
        return strtoupper($key);
    }

    return ucfirst($key);
}

/**
 * Load wpfd ui assets
 *
 * @param string $hook Hook name
 *
 * @return void
 */
function wpfd_admin_ux_load_assets($hook)
{
    if (strpos($hook, 'page_wpfd-config') === false) {
        return;
    }
    wp_register_script('wpfd-admin-ui-script-velocity', plugins_url('../assets/ui/js/velocity.min.js', __FILE__), array('jquery'), WPFD_VERSION);

    wp_enqueue_style('wpfd-admin-ui-style', plugins_url('../assets/ui/css/style.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-waves', plugins_url('../assets/ui/css/waves.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-admin-ui-style-configuration', plugins_url('../assets/ui/css/configuration.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('jquery-qtip-style', plugins_url('../assets/ui/css/jquery.qtip.css', __FILE__), array(), WPFD_VERSION, false);

    wp_register_script('wpfd-admin-ui-script-waves', plugins_url('../assets/ui/js/waves.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script', plugins_url('../assets/ui/js/script.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-serverfoldertree', plugins_url('../assets/js/serverfoldertree.js', __FILE__), array('jquery', 'wpfd-chosen'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-configuration', plugins_url('../assets/ui/js/configuration.js', __FILE__), array('jquery', 'wpfd-chosen', 'wpfd-admin-ui-script-serverfoldertree'), WPFD_VERSION);
    wp_register_script('wpfd-admin-ui-script-tabs', plugins_url('../assets/ui/js/tabs.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('jquery-qtip', plugins_url('../assets/ui/js/jquery.qtip.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    // Load fonts
    wp_enqueue_style('wpfd-admin-ui-font-nutiosans', plugins_url('../assets/ui/fonts/nutiosans.css', __FILE__));
    $scripts = array(
        'wpfd-admin-ui-script',
        'wpfd-admin-ui-script-serverfoldertree',
        'wpfd-admin-ui-script-configuration',
        'wpfd-admin-ui-script-velocity',
        'wpfd-admin-ui-script-tabs',
        'wpfd-admin-ui-script-waves',
        'jquery-qtip'
    );

    foreach ($scripts as $script) {
        wp_enqueue_script($script);
    }

    wp_localize_script('wpfd-admin-ui-script-configuration', 'wpfd_configuration_vars', array(
            'joomunited_connect_url' => admin_url('options-general.php#joomunited_connector')
    ));
}
add_action('admin_enqueue_scripts', 'wpfd_admin_ux_load_assets', 10, 1);

/**
 * Load wpfd statistics page assets
 *
 * @param string $hook Hook name
 *
 * @return void
 */
function wpfd_admin_statistics_load_assets($hook)
{
    if (strpos($hook, 'page_wpfd-statistics') === false) {
        return;
    }
    wp_enqueue_style('wpfd-admin-ui-font-nutiosans', plugins_url('../assets/ui/fonts/nutiosans.css', __FILE__));
    wp_enqueue_style('wpfd-admin-statistics', plugins_url('../assets/ui/css/statistics.css', __FILE__), array(), WPFD_VERSION);

    wp_register_script('wpfd-moment', plugins_url('../assets/ui/js/moment.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-daterangepicker', plugins_url('../assets/ui/js/daterangepicker.min.js', __FILE__), array('jquery'), WPFD_VERSION);
    wp_register_script('wpfd-admin-statistics', plugins_url('../assets/ui/js/statistics.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-chartjs', plugins_url('../assets/ui/js/chart.min.js', __FILE__), array(), WPFD_VERSION);
    wp_register_script('wpfd-chosen', plugins_url('app/admin/assets/js/chosen.jquery.min.js', WPFD_PLUGIN_FILE), array('jquery'), WPFD_VERSION);

    wp_enqueue_style('wpfd-daterangepicker-style', plugins_url('../assets/ui/css/daterangepicker.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-chartjs-style', plugins_url('../assets/ui/css/chart.min.css', __FILE__), array(), WPFD_VERSION);
    wp_enqueue_style('wpfd-chosen-style', plugins_url('app/admin/assets/css/chosen.css', WPFD_PLUGIN_FILE), array(), WPFD_VERSION);

    wp_enqueue_script('jquery');
    wp_enqueue_script('wpfd-moment');
    wp_enqueue_script('wpfd-daterangepicker');
    wp_enqueue_script('wpfd-admin-statistics');
    wp_enqueue_script('wpfd-chartjs');
    wp_enqueue_script('wpfd-chosen');
}
add_action('admin_enqueue_scripts', 'wpfd_admin_statistics_load_assets', 20, 1);
