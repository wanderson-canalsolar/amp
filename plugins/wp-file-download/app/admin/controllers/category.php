<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Controller;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Filesystem;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerCategory
 */
class WpfdControllerCategory extends Controller
{
    /**
     * Add new a category
     *
     * @return void
     */
    public function addCategory()
    {
        Utilities::getInput('type');
        $model = $this->getModel();
        $configModel = $this->getModel('config');
        $config = $configModel->getConfig();

        $categoryName =  Utilities::getInput('name', 'POST', 'string');

        if (is_null($categoryName) || empty($categoryName)) {
            $categoryName = esc_html__('New category', 'wpfd');
        }
        $parentId = Utilities::getInput('parentId', 'POST', 'int');
        if (is_null($parentId)) {
            $parentId = 0;
        }
        // Check term exists
        $termSpan = 0;
        $checkTitle = $categoryName;
        if (function_exists('term_exists')) {
            while (is_array(term_exists($checkTitle, 'wpfd-category', $parentId))) {
                $termSpan++;
                $checkTitle = $categoryName . ' ' . (string) $termSpan;
            }
        }
        if ($termSpan > 0) {
            $categoryName .= ' ' . (string) $termSpan;
        }
        if (wpfd_can_create_category()) {
            $id = $model->addCategory($categoryName, $parentId, $config['new_category_position']);

            if ($id) {
                $user_id = get_current_user_id();
                if ($user_id) {
                    $user_categories = get_user_meta($user_id, 'wpfd_user_categories', true);
                    if (is_array($user_categories)) {
                        if (!in_array($id, $user_categories)) {
                            $user_categories[] = $id;
                        }
                    } else {
                        $user_categories = array();
                        $user_categories[] = $id;
                    }
                    update_user_meta($user_id, 'wpfd_user_categories', $user_categories);
                }
                /**
                 * Action after new category created
                 *
                 * @param integer New category id
                 * @param string  Category created name
                 */
                do_action('wpfd_after_create_new_category', $id, $categoryName);
                $this->exitStatus(true, array('id_category' => $id, 'name' => $categoryName));
            }
        } else {
            $this->exitStatus(false, esc_html__('You don\'t have permission to create new category', 'wpfd'));
        }

        $this->exitStatus('error while adding category'); //todo: translate
    }

    /**
     * Rename category title
     *
     * @return void
     */
    public function setTitle()
    {
        $categoryId = Utilities::getInt('id_category', 'POST');
        $title = Utilities::getInput('title', 'POST', 'string');
        $model = $this->getModel();
        /**
         * Filter update category name
         *
         * @param string  New category name
         * @param integer Term id to change name
         *
         * @return string|boolean return false will not save new title
         */
        $title = apply_filters('wpfd_before_update_category_name', $title, $categoryId);
        if ($model->saveTitle($categoryId, $title)) {
            /**
             * Update category name
             *
             * @param integer Term id to change name
             * @param string  New category name
             */
            do_action('wpfd_update_category_name', $categoryId, $title);
            $this->exitStatus(true);
        }
        $this->exitStatus(esc_html__('Error while saving title', 'wpfd')); //todo: translate
    }

    /**
     * Save file params
     *
     * @return void
     */
    public function saveparams()
    {
        $modelRoles = $this->getModel('roles');
        $params = Utilities::getInput('params', 'POST', 'none');
        $id = Utilities::getInput('id', 'GET', 'int');
        $roles = isset($params['roles']) ? $params['roles'] : array();
        if (isset($params['visibility'])) {
            if (!$modelRoles->save($id, $params['visibility'], $roles)) {
                $this->exitStatus(false, 'error while saving');
            }
        }
        $model = $this->getModel();
        /**
         * Filter for category parameters before save to database
         *
         * @param array   Category params
         * @param integer Term id
         *
         * @return array
         */
        $params = apply_filters('wpfd_before_save_category', $params, $id);
        if (!$model->saveParams($id, $params)) {
            $this->exitStatus(false, esc_html__('Error while saving category\'s parameters', 'wpfd'));
        }
        /**
         * Action fire after save category parameters
         *
         * @param integer Term id
         * @param array   Category params
         */
        do_action('wpfd_save_category', $id, $params);
        $this->exitStatus(true);
    }

    /**
     * Change order categories
     *
     * @return void
     */
    public function changeOrder()
    {
        if (!wp_verify_nonce(Utilities::getInput('security', 'GET', 'none'), 'wpfd-security')) {
            $this->exitStatus(esc_html__('Wrong security Code!', 'wpfd'));
        }
        $pk = Utilities::getInt('pk');
        $ref = Utilities::getInt('ref');
        $position = Utilities::getInput('position', 'GET', 'string');
        $dragType = Utilities::getInput('dragType', 'GET', 'none');
        $model = $this->getModel();
        if ($model->changeOrder($pk, $ref, $position)) {
            if ($dragType === 'googledrive') {
                apply_filters('wpfdAddonGoogleDriveChangeOrder', $pk);
            } elseif ($dragType === 'dropbox') {
                apply_filters('wpfdAddonDropboxChangeOrder', $pk, $ref);
            } elseif ($dragType === 'onedrive') {
                apply_filters('wpfdAddonOneDriveChangeOrder', $pk);
            } elseif ($dragType === 'onedrive_business') {
                apply_filters('wpfdAddonOneDriveBusinessChangeOrder', $pk);
            }
            $this->exitStatus(true);
        }
        $this->exitStatus('problem');
    }

    /**
     * Order categories
     *
     * @return void
     */
    public function order()
    {
        if (Utilities::getInput('position') === 'after') {
            $position = 'after';
        } else {
            $position = 'first-child';
        }
        $pk = Utilities::getInt('pk');
        $ref = Utilities::getInt('ref');
        if ($ref === 0) {
            $ref = 1;
        }
        $model = $this->getModel();
        if ($model->move($pk, $ref, $position)) {
            $this->exitStatus(true, $pk . ' ' . $position . ' ' . $ref);
        }
        $this->exitStatus('problem');
    }

    /**
     * Delete category
     *
     * @return void
     */
    public function delete()
    {
        if (!wp_verify_nonce(Utilities::getInput('security', 'POST', 'none'), 'wpfd-security')) {
            $this->exitStatus(false, array('message' => 'Verify false!'));
        }
        if (!wpfd_can_delete_category()) {
            $this->exitStatus(esc_html__('You don\'t have permission to delete this category!', 'wpfd'));
        }
        $category = Utilities::getInt('id_category');
        Application::getInstance('Wpfd');
        $model = $this->getModel();

        $children = $model->getChildren($category);

        if ($model->delete($category)) {
            $children[] = $category;
            foreach ($children as $child) {
                $dir = WpfdBase::getFilesPath($child);
                WpfdTool::rrmdir($dir);
                if ($child === $category) {
                    continue;
                }
                $model->delete($child);
            }
            $this->exitStatus(true);
        }
        $this->exitStatus(esc_html__('Error while deleting category!', 'wpfd'));
    }

    /**
     * List categories for jaofiletree
     *
     * @return void
     */
    public function listdir()
    {
        $return = array();
        $dirs = array();
        $fi = array();

        if (!is_admin()) {
            echo json_encode(array());
        }

        $modelConfig = $this->getModel('config');
        $config = $modelConfig->getConfig();
        $allowed_ext = explode(',', $config['allowedext']);
        foreach ($allowed_ext as $key => $value) {
            $allowed_ext[$key] = strtolower(trim($allowed_ext[$key]));
            if ($allowed_ext[$key] === '') {
                unset($allowed_ext[$key]);
            }
        }

        $path = get_home_path() . DIRECTORY_SEPARATOR;

        $dir = Utilities::getInput('dir', 'GET', 'none');

        if (file_exists($path . $dir)) {
            $files = scandir($path . $dir);

            natcasesort($files);
            // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
            if (is_countable($files) && count($files) > 2) {
                // All dirs
                foreach ($files as $file) {
                    if (file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) &&
                        $file !== '.' && $file !== '..' && is_dir($path . $dir . DIRECTORY_SEPARATOR . $file)
                    ) {
                        $dirs[] = array('type' => 'dir', 'dir' => $dir, 'file' => $file);
                    } elseif (file_exists($path . $dir . DIRECTORY_SEPARATOR . $file) && $file !== '.' &&
                        $file !== '..' && !is_dir($path . $dir . DIRECTORY_SEPARATOR . $file) &&
                        in_array(wpfd_getext($file), $allowed_ext)
                    ) {
                        $fi[] = array(
                            'type' => 'file',
                            'dir' => $dir,
                            'file' => $file,
                            'ext' => strtolower(wpfd_getext($file))
                        );
                    }
                }
                $return = array_merge($dirs, $fi);
            }
        }
        echo json_encode($return);
        wp_die();
    }

    /**
     * Get category shortcode
     *
     * @throws Exception Throw when error
     *
     * @return void
     */
    public function getCategoryShortcode()
    {
        $app                  = Application::getInstance('Wpfd');
        $cateId               = Utilities::getInput('categoryId', 'GET', 'none');
        $catModel             = $this->getModel();
        $category             = $catModel->getCategory($cateId);
        $description          = (isset($category->description)) ? json_decode($category->description) : array();
        $title                = (isset($category->name)) ? $category->name : '';
        $theme                = (isset($description->theme)) ? $description->theme : 'default';
        $atts                 = (isset($cateId)) ? array('id' => $cateId) : array('id' => '');
        $path_helper          = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
        require_once $path_helper;
        $helper               = new WpfdHelperShortcodes();
        $category_shortcode   = $helper->categoryShortcode($atts);
        wp_send_json(array(
            'success' => true,
            'data'    => $category_shortcode,
            'title'   => $title,
            'theme'   => $theme
        ));
        die();
    }

    /**
     * Call search shortcode
     *
     * @throws Exception Throw when error
     *
     * @return void
     */
    public function callSearchShortcode()
    {
        $app                    = Application::getInstance('Wpfd');
        $categoryFilter         = Utilities::getInput('categoryFilter', 'GET', 'none');
        $tagFilter              = Utilities::getInput('tagFilter', 'GET', 'none');
        $tagAs                  = Utilities::getInput('tagAs', 'GET', 'none');
        $creationDateFilter     = Utilities::getInput('creationDateFilter', 'GET', 'none');
        $updateDateFilter       = Utilities::getInput('updateDateFilter', 'GET', 'none');
        $pageFilter             = Utilities::getInput('pageFilter', 'GET', 'none');
        $searchAtts             = array(
            'cat_filter'        => $categoryFilter,
            'tag_filter'        => $tagFilter,
            'display_tag'       => $tagAs,
            'create_filter'     => $creationDateFilter,
            'update_filter'     => $updateDateFilter,
            'file_per_page'     => $pageFilter
        );
        $path_helper            = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
        require_once $path_helper;
        $helper                 = new WpfdHelperShortcodes();
        $searchShortCode        = $helper->wpfdSearchShortcode($searchAtts);
        wp_send_json(array(
            'success' => true,
            'data'    => $searchShortCode
        ));
        die();
    }

    /**
    * Return category params
    *
    * @return void
    */
    public function getCategoryParams()
    {
        $catModel             = $this->getModel();
        $cateid               = Utilities::getInt('categoryId');
        $category             = $catModel->getCategory($cateid);
        $title                = (isset($category->name)) ? $category->name : '';
        $results              = array();
        $results['title']     = $title;

        wp_send_json(array(
            'success' => true,
            'data'    => $results
        ));
        die();
    }

    /**
     * Return edit category link
     *
     * @return void
     */
    public function editCategoryLink()
    {
        $editCategoryLink = esc_url(admin_url('admin.php?page=wpfd'));
        wp_send_json(array(
            'success' => true,
            'data'    => $editCategoryLink
        ));
        die();
    }
}
