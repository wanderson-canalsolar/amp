<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;

// Prohibit direct script loading
defined('ABSPATH') || die('No direct script access allowed!');

register_activation_hook(WPFD_PLUGIN_FILE, 'wpfd_install');
register_deactivation_hook(WPFD_PLUGIN_FILE, 'wpfd_deactivate');
register_uninstall_hook(WPFD_PLUGIN_FILE, 'wpfd_uninstall');

add_action('admin_init', 'wpfd_update');

if (!function_exists('wpfd_install')) {
    /**
     * Install plugin on Activate
     *
     * @return void
     */
    function wpfd_install()
    {
        global $wp_roles;
        if (!isset($wp_roles)) {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Overriding on null
            $wp_roles = new WP_Roles();
        }
        // Set permissions for editors and admins so they can do stuff with WPFD
        $wpfd_roles      = array('editor', 'administrator');
        $role_list       = (isset($wp_roles->role_objects)) ? $wp_roles->role_objects : array();
        $globalConfig    = get_option('_wpfd_global_config');
        if (!is_null(get_option('wpfd_version', null))) {
            update_option('_wpfd_installed', 'true');
        }
        foreach ($wpfd_roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $role->add_cap('wpfd_create_category');
                $role->add_cap('wpfd_edit_category');
                $role->add_cap('wpfd_edit_own_category');
                $role->add_cap('wpfd_delete_category');
                $role->add_cap('wpfd_manage_file');
            }
            if ($role && $role_name === 'administrator') {
                $role->add_cap('wpfd_edit_permission');
            }
        }

        if (!empty($role_list)) {
            foreach ($role_list as $roleName => $roleObj) {
                $current_role = get_role($roleName);
                if ($current_role) {
                    $current_role->add_cap('wpfd_download_files');
                    $current_role->add_cap('wpfd_preview_files');
                }
            }
        }

        if (isset($globalConfig) && is_array($globalConfig)) {
            $globalConfig['guest_download_files'] = 1;
            $globalConfig['guest_preview_files'] = 1;

            update_option('_wpfd_global_config', $globalConfig);
        }

        wpfd_create_page();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta(wpfd_get_schema());
        update_option('wpfd_version', WPFD_VERSION);
    }
}

if (!function_exists('wpfd_update')) {
    /**
     * Update database on update plugin
     *
     * @return void
     */
    function wpfd_update()
    {
        global $wpdb;
        $installedVersion = get_option('wpfd_version');

        $capVersion = get_option('_wpfd_cap_version', '1.0.0');
        if (version_compare($capVersion, '4.8.3', '<')) {
            $wpfd_roles = array('administrator');
            foreach ($wpfd_roles as $role_name) {
                $role = get_role($role_name);
                if ($role) {
                    $role->add_cap('wpfd_edit_permission');
                }
            }

            update_option('_wpfd_cap_version', '4.8.3');
        }
        $collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= 'DEFAULT CHARACTER SET ' . $wpdb->charset;
            }
            if (!empty($wpdb->collate)) {
                $collate .= ' COLLATE ' . $wpdb->collate;
            }
        }
        // db 1.1.0 to 4.3.14
        if (version_compare($installedVersion, '4.3.14', '<')) {
            $schemas = array();
            $schemas[] = /* @lang text */
                'CREATE TABLE ' . $wpdb->prefix . 'wpfd_tokens (
                id int(11) NOT NULL AUTO_INCREMENT,
                token varchar(32) NOT NULL,
                created_at INT UNSIGNED NOT NULL DEFAULT 0,
                file_id varchar(100) NOT NULL,
                PRIMARY KEY (`id`)
            ) ' . $collate . ' ;';
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($schemas);
            update_option('wpfd_version', WPFD_VERSION);
        }

        // Db update for 4.6.0
        if (version_compare($installedVersion, '4.6.0', '<')) {
            $schemas = array();

            $schemas[] = 'CREATE TABLE ' . $wpdb->prefix . 'wpfd_statistics (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `related_id` VARCHAR(50) NOT NULL,
                `uid` INT(11) NOT NULL DEFAULT \'0\',
                `type` VARCHAR(50) NOT NULL,
                `date` DATE NOT NULL DEFAULT \'0000-00-00\',
                `count` INT(11) NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`id`),
                KEY related_id (related_id),
                KEY uid (uid),
                KEY type (type),
                KEY date (date),
                KEY count (count)
        ) ' . $collate . ';';

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($schemas);
            update_option('wpfd_version', WPFD_VERSION);
        }
    }
}

if (!function_exists('wpfd_create_page')) {
    /**
     * Create a search page for search shortcode code
     *
     * @return boolean
     */
    function wpfd_create_page()
    {

        $option_search_page = '_wpfd_search_page_id';
        $search_page_id = get_option($option_search_page);

        if ($search_page_id > 0) {
            $page_object = get_post($search_page_id);

            if ('page' === $page_object->post_type && $page_object->ID) {
                return true;
            }
        }

        $page_data = array(
            'post_status' => 'publish',
            'post_type' => 'page',
//        'post_author'    => 1,
            'post_name' => 'wp-file-download-search',
            'post_title' => 'WP File download search',
            'post_content' => '[wpfd_search]',
            'comment_status' => 'closed'
        );
        $page_id = wp_insert_post($page_data);
        if ($page_id) {
            update_option($option_search_page, $page_id);
        }
    }
}

if (!function_exists('wpfd_deactivate_light_ver')) {
    /**
     * Deactivate light version
     *
     * @return void
     */
    function wpfd_deactivate_light_ver()
    {
        // Check if light version is installed or not
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
        $all_plugins = get_plugins();
        if (array_key_exists('wp-file-download-light/wp-file-download-light.php', $all_plugins)) {
            // If installed and activated, deactivate it
            if (is_plugin_active('wp-file-download-light/wp-file-download-light.php')) {
                deactivate_plugins('wp-file-download-light/wp-file-download-light.php');
            }
        }
    }
}
if (!function_exists('wpfd_uninstall')) {
    /**
     * Uninstall plugin
     *
     * @return void
     */
    function wpfd_uninstall()
    {
        $app = Application::getInstance('Wpfd', WPFD_PLUGIN_FILE);
        $app->init();
        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;
        $path_config = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'models';
        $path_config .= DIRECTORY_SEPARATOR . 'config.php';
        require_once $path_config;
        $modelConfig = new WpfdModelConfig;
        $params = $modelConfig->getConfig();

        if (WpfdBase::loadValue($params, 'deletefiles', 0)) {
            $path_wpfdtool = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'classes';
            $path_wpfdtool .= DIRECTORY_SEPARATOR . 'WpfdTool.php';
            require_once $path_wpfdtool;
            $wpfdTool = new WpfdTool;
            $wpfdTool->deleteAllData();
            $wp_fs = new WP_Filesystem_Base;
            $wp_fs->rmdir(WpfdBase::getFilesPath(), true);
            delete_option('wpfd_version');
        }
    }
}

if (!function_exists('wpfd_deactivate')) {
    /**
     * On deactivate plugin
     *
     * @return void
     */
    function wpfd_deactivate()
    {
        $tasks = array(
            'wpfd_remove_statistics_tasks',
            'wpfd_clean_junks_tasks'
        );

        foreach ($tasks as $task) {
            $timestamp = wp_next_scheduled($task);
            wp_unschedule_event($timestamp, $task);
        }
    }
}
if (!function_exists('wpfd_get_schema')) {
    /**
     * Get Table schema.
     *
     * @return array
     */
    function wpfd_get_schema()
    {
        global $wpdb;

        $collate = '';

        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) {
                $collate .= 'DEFAULT CHARACTER SET ' . $wpdb->charset;
            }
            if (!empty($wpdb->collate)) {
                $collate .= ' COLLATE ' . $wpdb->collate;
            }
        }
        $schemas[] = 'CREATE TABLE ' . $wpdb->prefix . 'wpfd_statistics (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `related_id` VARCHAR(50) NOT NULL,
                `uid` INT(11) NOT NULL DEFAULT \'0\',
                `type` VARCHAR(50) NOT NULL,
                `date` DATE NOT NULL DEFAULT \'0000-00-00\',
                `count` INT(11) NOT NULL DEFAULT \'0\',
                PRIMARY KEY (`id`),
                KEY related_id (related_id),
                KEY uid (uid),
                KEY type (type),
                KEY date (date),
                KEY count (count)
        ) ' . $collate . ';';
        $schemas[] = 'CREATE TABLE ' . $wpdb->prefix . 'wpfd_tokens (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `token` VARCHAR(32) NOT NULL,
                `created_at` INT(10) UNSIGNED NOT NULL DEFAULT \'0\',
                `file_id` VARCHAR(100) NOT NULL,
                PRIMARY KEY (`id`)
            ) ' . $collate . ';';

        return $schemas;
    }
}
