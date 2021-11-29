<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Controller;
use Joomunited\WPFramework\v1_0_5\Form;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Application;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerConfig
 */
class WpfdControllerConfig extends Controller
{
    /**
     * Set theme setting
     *
     * @return void
     */
    public function savetheme()
    {
        $model = $this->getModel();
        $themes = $model->getThemes();
        $theme = Utilities::getInput('selecttheme', 'POST');
        if (!in_array($theme, $themes)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$model->savetheme($theme)) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save theme params
     *
     * @return void
     */
    public function savethemeparams()
    {
        $model = $this->getModel();
        $theme = Utilities::getInput('theme', 'GET', 'none');
        if ((string)$theme === '') {
            $theme = 'default';
        }
        $form = new Form();
        if (WpfdBase::checkExistTheme($theme)) {
            $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'site';
            $formfile .= DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-' . $theme;
            $formfile .= DIRECTORY_SEPARATOR . 'form.xml';
        } else {
            $formfile = wpfd_locate_theme($theme, 'form.xml');
        }
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveThemeParams($theme, $datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Clone a theme
     *
     * @return void
     */
    public function clonetheme()
    {
        $model = $this->getModel();
        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'clone.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (isset($datas['theme_name']) && ($datas['theme_name'] === '')) {
            $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('Please, Enter theme name', 'wpfd'));
        }
        if (!$model->clonetheme($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        } else {
            $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('Clone theme successfully', 'wpfd'));
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save file params
     *
     * @return void
     */
    public function savetfileparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'file_config.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveFileParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save search params
     *
     * @return void
     */
    public function savesearchparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'search.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->saveSearchParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save admin config
     *
     * @return void
     */
    public function saveadminconfig()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('config_admin')) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->save($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        /**
         * Action fire after main settings are saved
         *
         * @internal
         *
         * @ignore
         */
        do_action('wpfd_after_main_setting_save');

        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save frontend config
     *
     * @return void
     */
    public function savefrontendconfig()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('config_frontend')) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->save($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        /**
         * Action fire after main settings are saved
         *
         * @internal
         *
         * @ignore
         */
        do_action('wpfd_after_main_setting_save');

        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save statistics config
     *
     * @return void
     */
    public function savestatisticsconfig()
    {
        $model = $this->getModel();

        $form = new Form();
        if (!$form->load('config_statistics')) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();

        if (!$model->save($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        /**
         * Action fire after main settings are saved
         *
         * @internal
         *
         * @ignore
         */
        do_action('wpfd_after_main_setting_save');

        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }
    /**
     * Save role
     *
     * @return void
     */
    public function saveroles()
    {
        global $wp_roles;

        if (!isset($_POST['wpfd_role_nonce']) ||
            !check_admin_referer('wpfd_role_settings', 'wpfd_role_nonce') ||
            !current_user_can('manage_options')) {
            return;
        }
        $role_caps          = get_option('_wpfd_role_caps', array());
        $globalConfig       = get_option('_wpfd_global_config');
        $guest_role_caps    = Utilities::getInput('guest', 'POST', 'none');
        if (!isset($wp_roles)) {
            // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Overriding on null
            $wp_roles = new WP_Roles();
        }
        $roles          = $wp_roles->role_objects;
        $roles_names    = $wp_roles->role_names;

        $post_type      = get_post_type_object('wpfd_file');
        $post_type_caps = (array)$post_type->cap;
        $wpfdPermission = array(
            'wpfd_create_category',
            'wpfd_edit_category',
            'wpfd_edit_own_category',
            'wpfd_delete_category',
            'wpfd_manage_file',
            'wpfd_edit_permission',
            'wpfd_download_files',
            'wpfd_preview_files',
        );

        foreach ($roles as $user_role => $role) {
            $user_role_caps = Utilities::getInput($role->name, 'POST', 'none');
            foreach ($post_type_caps as $post_key => $post_cap) {
                if (isset($user_role_caps[$post_key]) && ($user_role_caps[$post_key] === 'on' || (int) $user_role_caps[$post_key] === 1)) {
                    $role->add_cap($post_key);
                    if ($post_key === 'wpfd_download_files' && $role->name !== 'guest') {
                        $globalConfig[$role->name . '_download_files'] = 1;
                    }
                    if ($post_key === 'wpfd_preview_files' && $role->name !== 'guest') {
                        $globalConfig[$role->name . '_preview_files'] = 1;
                    }
                } else {
                    if (in_array($post_key, $wpfdPermission)) {
                        $role->remove_cap($post_key);
                        if ($post_key === 'wpfd_download_files' && $role->name !== 'guest') {
                            $globalConfig[$role->name . '_download_files'] = 0;
                        }
                        if ($post_key === 'wpfd_preview_files' && $role->name !== 'guest') {
                            $globalConfig[$role->name . '_preview_files'] = 0;
                        }
                    }
                }
                update_option('_wpfd_global_config', $globalConfig);
            }
        }

        if (!is_null($guest_role_caps)) {
            if ((isset($guest_role_caps['wpfd_download_files']) && (int)$guest_role_caps['wpfd_download_files'] === 1) ||
                (isset($guest_role_caps['wpfd_download_files']) && $guest_role_caps['wpfd_download_files'] === 'on')) {
                $globalConfig['guest_download_files'] = 1;
            } else {
                $globalConfig['guest_download_files'] = 0;
            }

            if ((isset($guest_role_caps['wpfd_preview_files']) && (int)$guest_role_caps['wpfd_preview_files'] === 1) ||
                (isset($guest_role_caps['wpfd_preview_files']) && $guest_role_caps['wpfd_preview_files'] === 'on')) {
                $globalConfig['guest_preview_files'] = 1;
            } else {
                $globalConfig['guest_preview_files'] = 0;
            }
        } else {
            $globalConfig['guest_download_files'] = 0;
            $globalConfig['guest_preview_files']  = 0;
        }

        update_option('_wpfd_global_config', $globalConfig);
        $this->redirect('admin.php?page=wpfd-config#wpfd-user-roles');

        wp_die();
    }
    /**
     * Save notifications params
     *
     * @return void
     */
    public function savenotificationsparams()
    {
        $model = $this->getModel('notification');

        $form     = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'notifications.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-notification&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-notification&error=2');
        }
        $datas                                 = $form->sanitize();
        $datas['notify_add_event_editor']      = Utilities::getInput('notify_add_event_editor', 'POST', 'none');
        $datas['notify_edit_event_editor']     = Utilities::getInput('notify_edit_event_editor', 'POST', 'none');
        $datas['notify_delete_event_editor']   = Utilities::getInput('notify_delete_event_editor', 'POST', 'none');
        $datas['notify_download_event_editor'] = Utilities::getInput('notify_download_event_editor', 'POST', 'none');
        if (!$model->saveNotifications($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3#email_notication_editor');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd') . '#email_notication_editor');
    }

    /**
     * Save mail option params
     *
     * @return void
     */
    public function savemailoption()
    {
        $model = $this->getModel('notification');

        $form     = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'mail_option.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1#mail_option');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2#mail_option');
        }
        $datas = $form->sanitize();
        if (!$model->saveMailOption($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3#mail_option');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd') . '#mail_option');
    }
    /**
     * Save upload params
     *
     * @return void
     */
    public function saveuploadparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'upload.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveUploadParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save file in cate setting
     *
     * @return void
     */
    public function savefilecatparams()
    {
        $model = $this->getModel();

        $form = new Form();
        $formfile = Application::getInstance('Wpfd')->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $formfile .= 'forms' . DIRECTORY_SEPARATOR . 'file_cat_sortcode.xml';
        if (!$form->load($formfile)) {
            $this->redirect('admin.php?page=wpfd-config&error=1');
        }
        if (!$form->validate()) {
            $this->redirect('admin.php?page=wpfd-config&error=2');
        }
        $datas = $form->sanitize();
        if (!$model->saveFileInCatParams($datas)) {
            $this->redirect('admin.php?page=wpfd-config&error=3');
        }
        $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
    }

    /**
     * Save folder import export setting
     *
     * @return void
     */
    public function savefolderimportexportparams()
    {
        $importFile         = Utilities::getInput('wpfd_import_folders_btn', 'POST', 'none');
        $importExportSave   = Utilities::getInput('wpfd-import-export-save', 'POST', 'none');

        if (isset($importExportSave)) {
            $export_val                     = Utilities::getInput('export_folder_type', 'POST', 'none');
            $export_type                    = ( isset($export_val) ) ? $export_val : 'only_folder';
            $config                         = get_option('_wpfd_global_config');
            $config['export_folder_type']   = $export_type;
            update_option('_wpfd_global_config', $config);
            $this->redirect('admin.php?page=wpfd-config&msg=' . esc_html__('success', 'wpfd'));
        } else {
            $disc           = Utilities::getInput('wpfd-import-xml-disc', 'POST', 'none');
            $upload         = $this->wpfdHandleUpload();
            if (isset($upload) && is_array($upload) && !empty($upload)) {
                $config                         = get_option('_wpfd_global_config');
                $config['import_file_params']   = $upload;
                $config['import_xml_disc']      = $disc;
                update_option('_wpfd_global_config', $config);
            }
            $this->redirect('admin.php?page=wpfd-config');
        }
    }

    /**
     * Get Token of Dropbox on authenticate
     *
     * @return void
     */
    public function getTokenKey()
    {
        $dropAuthor = Utilities::getInput('dropAuthor', 'POST', 'string');

        $app = Application::getInstance('WpfdAddon');
        $path_wpfdaddondropbox = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType();
        $path_wpfdaddondropbox .= DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'WpfdAddonDropbox.php';
        require_once $path_wpfdaddondropbox;
        $dropbox = new WpfdAddonDropbox();

        if (!empty($dropAuthor)) {
            //convert code authorCOde to Token
            try {
                $list = $dropbox->convertAuthorizationCode($dropAuthor);
            } catch (Exception $ex) {
                $this->exitStatus(false, esc_html__('The Authorization Code are Wrong!', 'wpfd'));
            }
        } else {
            $this->exitStatus(false, esc_html__('The Authorization code could not be empty!', 'wpfd'));
        }
        if (!isset($list)) {
            $list = array();
        }
        if ($list['accessToken']) {
            $app = Application::getInstance('WpfdAddon');
            $path_wpfdhelper = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers';
            $path_wpfdhelper .= DIRECTORY_SEPARATOR . 'WpfdHelper.php';
            require_once $path_wpfdhelper;
            //save accessToken to database
            $saveParams = new WpfdAddonHelper();
            $params = $saveParams->getAllDropboxConfigs();
            $params['dropboxToken'] = $list['accessToken'];
            $saveParams->saveDropboxConfigs($params);
        } else {
            $this->exitStatus(false, esc_html__('The Authorization Code are Wrong!', 'wpfd'));
        }
        $this->exitStatus(true, $list);
    }

    /**
     * Get all versions to delete
     *
     * @param boolean $return Return array or json
     *
     * @return array
     */
    public function prepareVersions($return = false)
    {
        global $wpdb;
        if (!wp_verify_nonce(Utilities::getInput('security', 'POST', 'none'), 'wpfd-security')) {
            wp_send_json(array('success' => false, 'message' => esc_html__('Wrong security code!', 'wpfd')));
        }

        $metas = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT DISTINCT pm.post_id, tt.term_id FROM ' . $wpdb->postmeta . ' AS pm
                 INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.object_id = pm.post_id
                 INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                WHERE pm.meta_key = %s
                AND tt.taxonomy = %s',
                '_wpfd_file_versions',
                'wpfd-category'
            )
        );

        if (!$return) {
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            $total = is_countable($metas) ? count($metas) : 0;
            if ($total > 0) {
                wp_send_json(array('success' => true, 'total' => $total));
            } else {
                wp_send_json(array('success' => false, 'message' => esc_html__('No versions to delete!', 'wpfd')));
            }
        } else {
            $files = array();
            if (!is_wp_error($metas) && !empty($metas)) {
                foreach ($metas as $meta) {
                    $files[] = array(
                        'id' => $meta->post_id,
                        'catId' => $meta->term_id
                    );
                }
            }

            return $files;
        }
    }

    /**
     * Delete all files versions
     *
     * @return void
     */
    public function purgeVersions()
    {
        if (!wp_verify_nonce(Utilities::getInput('security', 'POST', 'none'), 'wpfd-security')) {
            wp_send_json(array('success' => false, 'message' => esc_html__('Wrong security code!', 'wpfd')));
        }
        $keep = Utilities::getInt('keep', 'POST');

        if ((int) $keep > 100) {
            $keep = 100;
        }

        $versions = $this->prepareVersions(true);

        if (is_array($versions) && !empty($versions)) {
            Application::getInstance('Wpfd');
            $fileModel = $this->getModel('file');
            foreach ($versions as $file) {
                $fileModel->deleteOldVersions($file['id'], $file['catId'], $keep);
            }
            wp_send_json(array('success' => true));
        } else {
            wp_send_json(array('success' => false, 'message' => esc_html__('No versions to delete!', 'wpfd')));
        }
    }

    /**
     * Get export params
     *
     * @return array
     */
    public function getExportParams()
    {
        $args = array();
        $args['include_childs'] = 1;
        $defaults = array(
            'content'       => 'attachment',
            'author'        => false,
            'category'      => false,
            'start_date'    => false,
            'end_date'      => false,
            'status'        => false,
        );
        $args = wp_parse_args($args, $defaults);
        return $args;
    }

    /**
     * Set export exclude folders
     *
     * @return void
     */
    public function setExportExcludeFolders()
    {
        $exclude_val = Utilities::getInput('wpfd_export_folder_term_ids', 'POST', 'none');
        $exclude_ids = ( isset($exclude_val) ) ? explode(',', $exclude_val) : array();
        if (!empty($exclude_ids)) {
            $config                             = get_option('_wpfd_global_config');
            $config['export_exclude_term_ids']  = $exclude_ids;
            update_option('_wpfd_global_config', $config);
        }
    }

    /**
     * Get term parent
     *
     * @param integer $id      Folder id
     * @param array   $results List folder
     *
     * @return array
     */
    public function getTermsParent($id, $results)
    {
        $parent     = get_term($id);
        $results[]  = $parent;
        if (!empty($parent->parent) || (int) $parent->parent !== 0) {
            $results = $this->getTermsParent($parent->parent, $results);
        }

        return $results;
    }

    /**
     * Get all terms need import
     *
     * @param array   $include Array or comma/space-separated string of term ids to include
     * @param integer $parent  ID of term parent
     * @param array   $results Result
     *
     * @return array
     */
    public function getTermChild($include, $parent = false, $results = array())
    {
        $args = array(
            'get'                    => 'all',
            'taxonomy'               => 'wpfd-category',
            'hide_empty'             => false,
            'meta_query'             => array(
                array(
                    'key'     => 'wpfd_drive_type',
                    'compare' => 'NOT EXISTS'
                ),
            ),
        );

        if (!empty($parent)) {
            $args['parent'] = $parent;
        } else {
            $args['include'] = $include;
        }

        $term_query = new WP_Term_Query($args);
        $terms      = ( isset($term_query->terms) ) ? $term_query->terms : array();

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $results[] = $term;
                if ((int) $term->parent !== 0) {
                    $results = $this->getTermsParent($term->parent, $results);
                }
                $results = $this->getTermChild($include, $term->term_id, $results);
            }
        }

        return $results;
    }

    /**
     * Export folder structure
     *
     * @return void
     */
    public function exportFolder()
    {

        if (!defined('WXR_VERSION')) {
            define('WXR_VERSION', '1.2');
        }
        set_time_limit(0);
        $config          = get_option('_wpfd_global_config');
        $export_type     = ( isset($config['export_folder_type']) ) ? $config['export_folder_type'] : 'only_folder';
        $include_folders = ( isset($config['export_exclude_term_ids']) ) ? $config['export_exclude_term_ids'] : array();
        // Load Wordpress export API
        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/functions/export.php');
        // Get params
        $args = $this->getExportParams();
        global $wpdb, $post;
        do_action('export_wp', $args);
        $sitename = sanitize_key(get_bloginfo('name'));
        if (!empty($sitename)) {
            $sitename .= '.';
        }
        $date        = date('Y-m-d');
        $wp_filename = $sitename . 'wordpress.' . $date . '.xml';
        $filename    = apply_filters('export_wp_filename', $wp_filename, $sitename, $date);
        if (is_null($filename)) {
            $filename = 'WPFD.export.xml';
        }

        header('Content-Description: File Transfer');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);

        $terms = array();
        $folders_id = array();
        switch ($export_type) {
            case 'all':
            case 'only_folder':
                $args = array(
                'get'                    => 'all',
                'taxonomy'               => 'wpfd-category',
                'hide_empty'             => false,
                'meta_query'             => array(
                    array(
                        'key'     => 'wpfd_drive_type',
                        'compare' => 'NOT EXISTS'
                    ),
                ),
                );

                $term_query = new WP_Term_Query($args);
                $folders    = ( isset($term_query->terms) ) ? $term_query->terms : array();
                break;
            case 'selection_folder':
                $folders = $this->getTermChild($include_folders);
                break;
        }

        if (!empty($folders)) {
            while ($folder = array_shift($folders)) {
                if ((int) $folder->parent === 0 || isset($terms[$folder->parent])) {
                    $terms[$folder->term_id] = $folder;
                    $folders_id[] = $folder->term_id;
                } else {
                    $folders[] = $folder;
                }
            }
        }

        if (count($terms) === 0) {
            $post_ids = false;
        } else {
            // Get content include
            switch ($export_type) {
                case 'all':
                    $post_ids = $wpdb->get_col('SELECT ID FROM ' . $wpdb->posts . ' WHERE post_type = "wpfd_file"');
                    break;
                case 'selection_folder':
                    $only_select_terms      = array();
                    $only_select_folders_id = array();
                    if (!empty($terms)) {
                        foreach ($terms as $term) {
                            if (isset($term->term_id) && in_array($term->term_id, $include_folders)) {
                                $only_select_terms[$term->term_id] = $term;
                            }
                        }
                        $terms = $only_select_terms;
                    }

                    if (!empty($folders_id)) {
                        foreach ($folders_id as $id) {
                            if (in_array($id, $include_folders)) {
                                $only_select_folders_id[] = $id;
                            }
                        }
                        $folders_id = $only_select_folders_id;
                    }

                // Get post query with selected terms
                    $post_ids = $wpdb->get_col('
                    SELECT ID FROM ' . $wpdb->posts . ' AS p
                     INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.object_id = p.ID 
                     INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id 
                     INNER JOIN ' . $wpdb->terms . ' AS t ON t.term_id = tt.term_id 
                     WHERE post_type = "wpfd_file" AND t.term_id IN (' . implode(',', $folders_id) . ')');
                    break;
                case 'only_folder':
                    $post_ids = false;
                    break;
            }
        }

        echo '<?xml version="1.0" encoding="' . esc_html(get_bloginfo('charset')) . "\" ?>\n";
        // phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped -- Render to XML file
        ?>
        <?php the_generator('export'); ?>
    <rss version="2.0"
         xmlns:excerpt="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/excerpt/"
         xmlns:content="http://purl.org/rss/1.0/modules/content/"
         xmlns:wfw="http://wellformedweb.org/CommentAPI/"
         xmlns:dc="http://purl.org/dc/elements/1.1/"
         xmlns:wp="http://wordpress.org/export/<?php echo WXR_VERSION; ?>/"
    >

        <channel>
            <title><?php bloginfo('name'); ?></title>
            <link><?php bloginfo('url'); ?></link>
            <description><?php bloginfo('description'); ?></description>
            <pubDate><?php echo date('D, d M Y H:i:s +0000'); ?></pubDate>
            <language><?php bloginfo('language'); ?></language>
            <wp:wxr_version><?php echo WXR_VERSION; ?></wp:wxr_version>
            <wp:base_site_url><?php echo wxr_site_url(); ?></wp:base_site_url>
            <wp:base_blog_url><?php bloginfo('url'); ?></wp:base_blog_url>

            <?php
            if ($post_ids) {
                wxr_authors_list($post_ids);
            }
            ?>

            <?php foreach ($terms as $t) : ?>
                <wp:term>
                    <wp:term_id><?php echo wxr_cdata($t->term_id); ?></wp:term_id>
                    <wp:term_taxonomy><?php echo wxr_cdata($t->taxonomy); ?></wp:term_taxonomy>
                    <wp:term_slug><?php echo wxr_cdata($t->slug); ?></wp:term_slug>
                    <wp:term_parent><?php echo wxr_cdata($t->parent ? $terms[$t->parent]->slug : ''); ?></wp:term_parent>
                    <?php wxr_term_name($t);
                    wxr_term_description($t);
                    wxr_term_meta($t); ?>
                </wp:term>
            <?php endforeach; ?>

            <?php
            // This action is documented in wp-includes/feed-rss2.php
            do_action('rss2_head');
            ?>

            <?php
            if ($post_ids) {
                // Get wpfd file url
                if (!class_exists('WpfdModelFilesfront')) {
                    include_once WPFD_PLUGIN_DIR_PATH . 'app/site/models/filesfront.php';
                }

                $files_model = new WpfdModelFilesfront();
                $files_list  = array();

                foreach ($terms as $term) {
                    $child_list = $files_model->getFiles((int) $term->term_id);
                    $files_list = array_merge($child_list, $files_list);
                }

                // @global WP_Query $wp_query
                global $wp_query;

                // Fake being in the loop.
                $wp_query->in_the_loop = true;

                // Fetch 20 posts at a time rather than loading the entire table into memory.
                while ($next_posts = array_splice($post_ids, 0, 20)) {
                    $where = 'WHERE ID IN (' . join(',', $next_posts) . ')';
                    // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Variable has been prepare
                    $attachments = $wpdb->get_results('SELECT * FROM ' . $wpdb->posts . ' ' . $where);

                    // Begin Loop.
                    foreach ($attachments as $attachment) {
                        setup_postdata($attachment);
                        $is_sticky = is_sticky($attachment->ID) ? 1 : 0;
                        ?>
                    <item>
                        <title>
                            <?php
                            // This filter is documented in wp-includes/feed.php
                            echo apply_filters('the_title_rss', $attachment->post_title);
                            ?>
                        </title>
                        <link><?php the_permalink_rss(); ?></link>
                        <pubDate><?php echo mysql2date('D, d M Y H:i:s +0000', get_post_time('Y-m-d H:i:s', true), false); ?></pubDate>
                        <dc:creator><?php echo wxr_cdata(get_the_author_meta('login')); ?></dc:creator>
                        <guid isPermaLink="false"><?php the_guid(); ?></guid>
                        <description></description>
                        <content:encoded>
                            <?php
                            /**
                             * Filters the post content used for WXR exports.
                             *
                             * @param string $post_content Content of the current post.
                             */
                            echo wxr_cdata(apply_filters('the_content_export', $attachment->post_content));
                            ?>
                        </content:encoded>
                        <excerpt:encoded>
                            <?php
                            /**
                             * Filters the post excerpt used for WXR exports.
                             *
                             * @param string $post_excerpt Excerpt for the current post.
                             */
                            echo wxr_cdata(apply_filters('the_excerpt_export', $attachment->post_excerpt));
                            ?>
                        </excerpt:encoded>
                        <wp:post_id><?php echo intval($attachment->ID); ?></wp:post_id>
                        <wp:post_date><?php echo wxr_cdata($attachment->post_date); ?></wp:post_date>
                        <wp:post_date_gmt><?php echo wxr_cdata($attachment->post_date_gmt); ?></wp:post_date_gmt>
                        <wp:comment_status><?php echo wxr_cdata($attachment->comment_status); ?></wp:comment_status>
                        <wp:ping_status><?php echo wxr_cdata($attachment->ping_status); ?></wp:ping_status>
                        <wp:post_name><?php echo wxr_cdata($attachment->post_name); ?></wp:post_name>
                        <wp:status><?php echo wxr_cdata($attachment->post_status); ?></wp:status>
                        <wp:post_parent><?php echo intval($attachment->post_parent); ?></wp:post_parent>
                        <wp:menu_order><?php echo intval($attachment->menu_order); ?></wp:menu_order>
                        <wp:post_type><?php echo wxr_cdata($attachment->post_type); ?></wp:post_type>
                        <wp:post_password><?php echo wxr_cdata($attachment->post_password); ?></wp:post_password>
                        <wp:is_sticky><?php echo intval($is_sticky); ?></wp:is_sticky>

                        <?php $attachment->has_url = false; ?>
                        <?php foreach ($files_list as $file) : ?>
                            <?php if ((int) $attachment->ID === (int) $file->ID) : ?>
                                <wp:attachment_url><?php echo wxr_cdata($file->linkdownload); ?></wp:attachment_url>
                                <?php $attachment->has_url = true; ?>
                                <?php //continue; ?>
                            <?php endif;?>
                        <?php endforeach;?>

                        <?php if (!$attachment->has_url) : ?>
                            <wp:attachment_url><?php echo wxr_cdata($attachment->guid); ?></wp:attachment_url>
                        <?php endif;?>

                        <?php unset($attachment->has_url); ?>
                        <?php wxr_post_taxonomy($attachment); ?>
                        <?php $postmeta = $wpdb->get_results($wpdb->prepare('SELECT * FROM ' . $wpdb->postmeta . ' WHERE post_id = %d', $attachment->ID));
                        foreach ($postmeta as $meta) :
                            /**
                             * Filters whether to selectively skip post meta used for WXR exports.
                             *
                             * Returning a truthy value to the filter will skip the current meta
                             * object from being exported.
                             *
                             * @param bool   $skip     Whether to skip the current post meta. Default false.
                             * @param string $meta_key Current meta key.
                             * @param object $meta     Current meta object.
                             */
                            if (apply_filters('wxr_export_skip_postmeta', false, $meta->meta_key, $meta)) {
                                continue;
                            }
                            ?>
                            <wp:postmeta>
                                <wp:meta_key><?php echo wxr_cdata($meta->meta_key); ?></wp:meta_key>
                                <wp:meta_value><?php echo wxr_cdata($meta->meta_value); ?></wp:meta_value>
                            </wp:postmeta>
                        <?php endforeach; ?>
                    </item>

                        <?php
                    }
                }
            }
            ?>

        </channel>
    </rss>
        <?php
        // phpcs:enable
        die();
    }

    /**
     * Handles the WXR upload and initial parsing of the file to prepare for
     * displaying author import options
     *
     * @return array
     */
    public function wpfdHandleUpload()
    {
        $file = wp_import_handle_upload();
        $error_message = '';
        if (isset($file['error'])) {
            $error_message .= '<p><strong>' . __('Sorry, there has been an error.', 'wpfd') . '</strong><br />';
            $error_message .= esc_html($file['error']) . '</p>';
            return array('status' => false, 'msg' => $error_message);
        } elseif (!file_exists($file['file'])) {
            $error_message .= '<p><strong>' . __('Sorry, there has been an error.', 'wpfd') . '</strong><br />';
            $error_message .= sprintf(__('The export file could not be found at <code>%s</code>. It is likely that this was caused by a permissions problem.', 'wpfd'), esc_html($file['file']));
            $error_message .= '</p>';
            return array('status' => false, 'msg' => $error_message);
        }
        $only_folder_val    = Utilities::getInput('import_only_folder', 'POST', 'none');
        $import_only_folder = (isset($only_folder_val)) ? $only_folder_val : false;
        return array('status' => true, 'path' => $file['file'], 'id' => (int)$file['id'], 'import_only_folder' => $import_only_folder);
    }

    /**
     * Run import folders
     *
     * @return void
     */
    public function wpfdRunImportFolders()
    {
        $path               = Utilities::getInput('path', 'POST', 'none');
        $id                 = Utilities::getInput('id', 'POST', 'none');
        $only_import_folder = Utilities::getInput('import_only_folder', 'POST', 'none');
        $categoryDisc       = Utilities::getInput('xml_category_disc', 'POST', 'none');
        $id                 = (int) $id;
        $only_import_folder = ( $only_import_folder === '1') ? 1 : false;

        require_once ABSPATH . 'wp-admin/includes/import.php';
        if (!class_exists('WP_Importer')) {
            $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
            if (file_exists($class_wp_importer)) {
                require $class_wp_importer;
            }
        }

        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/import/compat.php');
        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/import/parsers/class-wxr-parser.php');
        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/import/parsers/class-wxr-parser-simplexml.php');
        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/import/parsers/class-wxr-parser-xml.php');
        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/import/parsers/class-wxr-parser-regex.php');
        include_once(WPFD_PLUGIN_DIR_PATH . '/app/admin/classes/import/class-wp-import.php');

        global $wpfd_import;
        $wpfd_import = new WPFD_Import();
        $wpfd_import->start($path, $id, $only_import_folder, $categoryDisc);
        wp_send_json(array('status' => true, 'msg' => $wpfd_import->error_message));
    }

    /**
     * Reset import file params
     *
     * @return void
     */
    public function resetImportFileParams()
    {
        $config = get_option('_wpfd_global_config');
        if (isset($config['import_file_params'])) {
            unset($config['import_file_params']);
        }

        if (isset($config['import_xml_disc'])) {
            unset($config['import_xml_disc']);
        }

        update_option('_wpfd_global_config', $config);
        wp_send_json(array('success' => true, 'message' => esc_html__('reset is complete', 'wpfd')));
    }

    /**
     * Run import files, folders from servers
     *
     * @return void
     */
    public function wpfdRunImportServerFolders()
    {
        $list_import    =  Utilities::getInput('wpfd_list_import', 'POST', 'none');
        $categoryDisc   =  Utilities::getInput('server_category_disc', 'POST', 'none');
        $importOption   =  Utilities::getInput('server_import_option', 'POST', 'none');
        $exclude_terms  = array();
        $existsTerms    = array();
        if (!empty($list_import)) {
            if (in_array('', $list_import)) {
                $key_null = array_search('', $list_import);
                unset($list_import[$key_null]);
            }
            foreach ($list_import as $directory) {
                if ($directory !== '/') {
                    $path          = realpath(trailingslashit(get_home_path()) . $directory);
                    $parent        = ($categoryDisc !== '') ? (int)$categoryDisc : 0;
                    if (!file_exists($path)) {
                        continue;
                    }
                    if (!in_array($path, $exclude_terms)) {
                        $inserted_sub_terms = $this->wpfdImportCategoryFromServers($path, $parent, $importOption);
                        $exclude_terms      = array_merge($inserted_sub_terms['child_inserted'], $exclude_terms);
                        $existsTerms        = array_merge($inserted_sub_terms['existsTermList'], $existsTerms);
                    }
                }
            }
        }

        if (empty($existsTerms)) {
            wp_send_json(array('success' => true, 'existsTerms' => array()));
        } else {
            wp_send_json(array('success' => false, 'existsTerms' => $existsTerms));
        }
    }

    /**
     * WpfdGetAllFileFromServerFolders
     *
     * @param string $dir     Directory path
     * @param array  $results Contents
     *
     * @return array
     */
    public function wpfdGetAllFileFromServerFolders($dir, $results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } elseif ($value !== '.' && $value !== '..') {
                $this->wpfdGetAllFileFromServerFolders($path, $results);
                $results[] = $path;
            }
        }

        return $results;
    }

    /**
     * WpfdImportCategoryFromServers
     *
     * @param string  $path         Directory path
     * @param integer $parent       Category parent
     * @param string  $importOption Advanced import option
     *
     * @return array
     */
    public function wpfdImportCategoryFromServers($path, $parent = 0, $importOption = 'only_selected_folders')
    {
        $results        = Utilities::getInput('wpfd_list_import', 'POST', 'none');
        $results        = $this->wpfdMapImportPathList($results);
        $child_inserted = array();
        $existsTermList = array();
        $existsTermObj  = null;
        $args           = array(
            'taxonomy'   => 'wpfd-category',
            'orderby'    => 'name',
            'order'      => 'ASC',
            'hide_empty' => 0,
        );
        $terms          = get_terms($args);
        $path_infos     = pathinfo($path);
        $name           = $path_infos['filename'];
        $slug           = sanitize_title($name);
        $term_exists    = 0;
//        $existsTermId   = term_exists($slug, 'wpfd-category');

        if (!empty($terms)) {
            foreach ($terms as $term) {
                if ($term->name === $name && $term->slug === $slug && (int)$parent === (int)$term->parent) {
                    $term_exists = 1;
                    $existsTermList[] = $name;
                    $existsTermObj = $term;
                    continue;
                }
            }
        }

        // Import category
        if (!$term_exists) {
            $inserted       = wp_insert_term($name, 'wpfd-category', array('slug' => $slug, 'parent' => $parent));
            $currentTermId  = $inserted['term_id'];
        } else {
            $currentTermId  = $existsTermObj->term_id;
        }
        $files          = $this->wpfdGetAllFileFromServerFolders($path, array());
        // Import files
        if (!empty($files) && !$term_exists) {
            $this->wpfdImportFiles((int)$currentTermId, $files);
        }
        // Import sub categories, files
        $directories    = glob($path . '/*', GLOB_ONLYDIR);
        if (!empty($directories)) {
            foreach ($directories as $direct) {
                if ($importOption === 'all_sub_folders') {
                    $child_inserted2  = $this->wpfdImportCategoryFromServers($direct, (int)$currentTermId, 'all_sub_folders');
                    $child_inserted   = array_merge($child_inserted2['child_inserted'], $child_inserted);
                    $child_inserted[] = $direct;
                    $existsTermList   = array_merge($child_inserted2['existsTermList'], $existsTermList);
                } else {
                    if (in_array($direct, $results)) {
                        $child_inserted2  = $this->wpfdImportCategoryFromServers($direct, (int)$currentTermId, 'only_selected_folders');
                        $child_inserted   = array_merge($child_inserted2['child_inserted'], $child_inserted);
                        $child_inserted[] = $direct;
                        $existsTermList   = array_merge($child_inserted2['existsTermList'], $existsTermList);
                    }
                }
            }
        }

        return array('child_inserted' => $child_inserted, 'existsTermList' => $existsTermList);
    }

    /**
     * WpfdImportFiles
     *
     * @param integer $categoryId Category id
     * @param array   $files      File list
     *
     * @return void
     */
    public function wpfdImportFiles($categoryId, $files)
    {
        if ((int)$categoryId > 0) {
            $file_dir = WpfdBase::getFilesPath($categoryId);
            if (!file_exists($file_dir)) {
                mkdir($file_dir, 0777, true);
                $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                $file = fopen($file_dir . 'index.html', 'w');
                fwrite($file, $data);
                fclose($file);
                $data = 'deny from all';
                $file = fopen($file_dir . '.htaccess', 'w');
                fwrite($file, $data);
                fclose($file);
            }

            if (!empty($files)) {
                $count       = 0;
                $configModel = $this->getModel('config');
                $allowed     = $configModel->getAllowedExt();
                foreach ($files as $file) {
                    if (in_array(wpfd_getext($file), $allowed)) {
                        $newname    = uniqid() . '.' . strtolower(wpfd_getext($file));
                        copy($file, $file_dir . $newname);
                        chmod($file_dir . $newname, 0777);
                        $filesModel = $this->getModel('files');
                        setlocale(LC_ALL, 'C.UTF-8');
                        $id_file    = $filesModel->addFile(array(
                            'title'         => preg_replace('#\.[^.]*$#', '', basename($file)),
                            'id_category'   => $categoryId,
                            'file'          => $newname,
                            'ext'           => strtolower(wpfd_getext($file)),
                            'size'          => filesize($file_dir . $newname)
                        ));
                        if (!$id_file) {
                            unlink($file_dir . $newname);
                        }
                        $count++;
                    }
                }
            }
        }
    }

    /**
     * WpfdMapImportPathList
     *
     * @param array $importList List directory import
     *
     * @return array Result list
     */
    public function wpfdMapImportPathList($importList)
    {
        $results = array();

        if (!empty($importList)) {
            foreach ($importList as $order) {
                if ($order !== '/') {
                    $folder_path = realpath(trailingslashit(get_home_path()) . $order);
                    $results[] = $folder_path;
                }
            }
        }

        return $results;
    }

    /**
     * WpfdRunImportDownloadManagerFolders
     *
     * @return void
     */
    public function wpfdRunImportDownloadManagerFolders()
    {
        $args = array(
            'taxonomy'      => 'wpdmcategory',
            'orderby'       => 'term_group',
            'hierarchical'  => true,
            'hide_empty'    => 0,
            'parent'        => 0
        );
        $wpdmCategoryList       = get_terms($args);
        $selectedWpdmCategory   = Utilities::getInput('selected_wpdm_category', 'POST', 'none');
        $disc                   = Utilities::getInput('wpdm_category_disc', 'POST', 'none');
        $categoryDisc           = ($disc !== '') ? (int)$disc : 0;
        $importedCategory       = array();
        $cleanList              = array();
        $termExists             = array();
        if ($selectedWpdmCategory === 'all') {
            if (!empty($wpdmCategoryList)) {
                foreach ($wpdmCategoryList as $category) {
                    $cleanList[] = $category;
                    $childrenIds = get_term_children((int)$category->term_id, 'wpdmcategory');
                    if (!empty($childrenIds)) {
                        foreach ($childrenIds as $id) {
                            $childTerm = get_term((int)$id);
                            if ($childTerm) {
                                $cleanList[] = $childTerm;
                            }
                        }
                    }
                }
            }

            if (!empty($cleanList)) {
                foreach ($cleanList as $term) {
                    $newId              = $this->wpfdImportDownloadManagerControls((int)$term->term_id, $categoryDisc, (int)$term->parent, $importedCategory);
                    if (isset($newId['term_exists'])) {
                        $termExists[]   = $newId['term_exists'];
                    } else {
                        $importedCategory = array_replace($newId, $importedCategory);
                    }
                }
            }
        } else {
            $newParentId = $this->wpfdImportDownloadManagerControls((int)$selectedWpdmCategory, $categoryDisc, 0, array());
            if (isset($newParentId['term_exists'])) {
                $termExists[] = $newParentId['term_exists'];
            }
        }

        if (empty($termExists)) {
            wp_send_json(array('success' => true, 'data' => $termExists));
        } else {
            wp_send_json(array('success' => false, 'data' => $termExists));
        }
    }

    /**
     * WpfdImportDownloadManagerControls
     *
     * @param integer $categoryId   Category id
     * @param integer $categoryDisc Disc
     * @param integer $parent       Category parent
     * @param array   $importedList Imported category list
     *
     * @return void|array
     */
    public function wpfdImportDownloadManagerControls($categoryId, $categoryDisc, $parent = 0, $importedList = array())
    {
        if ((int)$categoryId <= 0) {
            return;
        }

        $insertedCategoryIds = array();
        if ((int)$parent === 0) {
            $insertedCategoryIds = $this->wpfdDownloadManagerCategoryImport($categoryId, $categoryDisc, $parent);
        } else {
            if (!empty($importedList)) {
                foreach ($importedList as $key => $value) {
                    if ((int)$key === (int)$parent) {
                        $parent = (int)$value;
                        $insertedCategoryIds = $this->wpfdDownloadManagerCategoryImport($categoryId, $categoryDisc, $parent);
                        continue;
                    }
                }
            }
        }

        return $insertedCategoryIds;
    }

    /**
     * WpfdDownloadManagerCategoryImport
     *
     * @param integer $cateId       Category id
     * @param integer $categoryDisc Disc
     * @param integer $cateParent   Category parent
     *
     * @return array
     */
    public function wpfdDownloadManagerCategoryImport($cateId, $categoryDisc, $cateParent = 0)
    {
        global $wpdb;
        $content_dir = str_replace('\\', '/', WP_CONTENT_DIR);
        if (!defined('WPDM_UPLOAD_DIR')) {
            define('WPDM_UPLOAD_DIR', $content_dir.'/uploads/download-manager-files/');
        }

        $wpdm_term          = get_term($cateId);
        $newIds             = array();
        $newFilePath        = array();
        $term_exists        = 0;
        if ((int)$categoryDisc !== 0 && (int)$cateParent === 0) {
            $cateParent = (int)$categoryDisc;
        }

        if ($wpdm_term) {
            $args = array(
                'taxonomy'   => 'wpfd-category',
                'hide_empty' => 0
            );
            $terms              = get_terms($args);
            $categoryName       = $wpdm_term->name;
            $categorySlug       = sanitize_title($categoryName);
            if (!empty($terms)) {
                foreach ($terms as $term) {
                    if ($term->name === $categoryName && $term->slug === $categorySlug && (int)$term->parent === (int)$cateParent) {
                        $term_exists = 1;
                        continue;
                    }
                }
            }

            if (!$term_exists) {
                $inserted           = wp_insert_term($categoryName, 'wpfd-category', array('slug' => $categorySlug, 'parent' => $cateParent));
                $newIds[$cateId]    = (int)$inserted['term_id'];
                $baseSite           = get_site_url();
                $files              = $wpdb->get_results($wpdb->prepare(
                    'SELECT ID, post_title FROM ' . $wpdb->posts . ' AS p
                     INNER JOIN ' . $wpdb->term_relationships . ' AS tr ON tr.object_id = p.ID
                     INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt ON tt.term_taxonomy_id = tr.term_taxonomy_id
                     INNER JOIN ' . $wpdb->terms . ' AS t ON t.term_id = tt.term_id
                     WHERE post_type = "wpdmpro" AND t.term_id = ' . $cateId
                ));

                if (!empty($files)) {
                    foreach ($files as $file) {
                        $metaName = get_post_meta($file->ID, '__wpdm_files', true);
                        if (!empty($metaName)) {
                            if (file_exists(WPDM_UPLOAD_DIR . $metaName[0])) {
                                $file->file_path = WPDM_UPLOAD_DIR . $metaName[0];
                            } else {
                                if (file_exists(ABSPATH . $metaName[0])) {
                                    $file->file_path = ABSPATH . $metaName[0];
                                } else {
                                    if (strpos($metaName[0], $baseSite) !== false) {
                                        $file->file_path = realpath(ABSPATH . str_replace($baseSite, '', $metaName[0]));
                                    }
                                }
                            }
                        }
                        if (isset($file->file_path)) {
                            $newFilePath[] = $file->file_path;
                        }
                    }

                    if (isset($inserted['term_id']) && (int)$inserted['term_id'] > 0 && !empty($newFilePath)) {
                        $this->wpfdImportFiles((int)$inserted['term_id'], $newFilePath);
                    }
                }
            } else {
                $existTerms['term_exists'] = $categoryName;
                return $existTerms;
            }
        }

        return $newIds;
    }

    /**
     * WpfdListAllCategories
     *
     * @return void
     */
    public function wpfdListAllCategories()
    {

        $args = array(
            'taxonomy'      => 'wpfd-category',
            'orderby'       => 'term_group',
            'hierarchical'  => true,
            'hide_empty'    => 0,
            'parent'        => 0
        );
        $wpfdCategories = get_terms($args);

        if (!empty($wpfdCategories)) {
            $results    = array();
            $cleanList  = array();
            foreach ($wpfdCategories as $category) {
                $category->level    = 0;
                $type = get_term_meta($category->term_id, 'wpfd_drive_type', true);
                if ((string) $type === '') {
                    $cleanList[]    = $category;
                    $list           = $this->wpfdGetCategoriesLevel($category, $results);
                    if (!empty($list)) {
                        foreach ($list as $term) {
                            $cleanList[] = $term;
                        }
                    }
                }
            }

            wp_send_json(array('data' => $cleanList, 'success' => true));
        } else {
            wp_send_json(array('data' => array(), 'success' => false));
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
    public function wpfdGetCategoriesLevel($rootCategory, $results)
    {
        if (!is_array($results)) {
            $results = array();
        }
        $categories = get_terms('wpfd-category', 'orderby=term_group&hierarchical=1&hide_empty=0&parent='. $rootCategory->term_id);
        if ($categories) {
            foreach ($categories as $cat) {
                $cat->level = $rootCategory->level + 1;
                $results[]  = $cat;
                $results    = $this->wpfdGetCategoriesLevel($cat, $results);
            }
        }

        return $results;
    }

    /**
     * WpfdSaveImportExportParams
     *
     * @return void
     */
    public function wpfdSaveImportExportParams()
    {
        $exportType = Utilities::getInput('export_type', 'GET', 'none');
        $config     = get_option('_wpfd_global_config');
        if (!isset($config['export_folder_type'])) {
            $config['export_folder_type'] = $exportType;
        } else {
            if ($config['export_folder_type'] !== $exportType) {
                $config['export_folder_type'] = $exportType;
            }
        }

        update_option('_wpfd_global_config', $config);
        wp_send_json(array('success' => true, 'message' => 'Saved!'));
    }
}