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
use Joomunited\WPFramework\v1_0_5\Form;
use Joomunited\WPFramework\v1_0_5\Model;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerFile
 */
class WpfdControllerFile extends Controller
{
    /**
     * Download file
     *
     * @return void
     */
    public function download()
    {
        Application::getInstance('Wpfd');
        $model = $this->getModel();
        $id = Utilities::getInt('id');
        $version = Utilities::getInput('version', 'GET', 'string');
        $catid = Utilities::getInt('catid');
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $catid);

        if (in_array($categoryFrom, wpfd_get_support_cloud())) {
            $id_file = Utilities::getInput('id', 'GET', 'string');
            $vid = Utilities::getInput('vid', 'GET', 'string');
            if ($version) {
                /**
                 * Filters to download version
                 *
                 * @param string File id
                 * @param string Version id
                 * @param string Category from
                 *
                 * @ignore
                 *
                 * @internal
                 *
                 * @return void
                 */
                $version = apply_filters('wpfd_addon_download_version', $id_file, $vid, $categoryFrom);
                if ($version) {
                    // todo: apply download large file
                    header('Content-Description: File Transfer');
                    header('Content-Type: ' . $version['filetype']);
                    header('Content-Disposition: attachment; filename="' . $version['filename'] . '"');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate');
                    header('Pragma: public');
                    header('Content-Length: ' . $version['filesize']);
                    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print file content as is
                    echo $version['content'];
                }
            }
            exit();
        } else {
            $file = $model->getFile($id);
            $remote_url = false;
            $url = '';
            if (!$version) {
                $file = $model->getFile($id);
                $file_meta = get_post_meta($id, '_wpfd_file_metadata', true);
                $remote_url = isset($file_meta['remote_url']) ? $file_meta['remote_url'] : false;
                $url = $file_meta['file'];
            } else {
                $vid = Utilities::getInt('vid');
                $version = $model->getVersion($vid);
                if ($version) {
                    $file = array_merge($file, $version);
                    if ($version['remote_url']) {
                        $remote_url = true;
                        $url = $version['file'];
                    }
                }
            }

            //todo : verifier les droits d'acces à la catéorgie du fichier
            if (!WpfdHelperFile::checkAccess($file)) {
                exit();
            }
            if (!empty($file) && $file['ID']) {
                $filename = WpfdHelperFile::santizeFileName($file['title']);
                if ($filename === '') {
                    $filename = 'download';
                }
                if ($remote_url) {
                    header('Location: ' . $url);
                } else {
                    $sysfile = WpfdBase::getFilesPath($file['catid']) . '/' . $file['file'];
                    WpfdHelperFile::sendDownload(
                        $sysfile,
                        basename($filename . '.' . $file['ext']),
                        $file->ext
                    );
                }
            }
            exit();
        }
    }

    /**
     * Restore file
     *
     * @return void
     */
    public function restore()
    {
        $id_file = Utilities::getInt('id');
        $vid = Utilities::getInt('vid');
        $catid = Utilities::getInt('catid');
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $catid);
        if (in_array($categoryFrom, wpfd_get_support_cloud())) {
            $id_file = Utilities::getInput('id', 'GET', 'string');
            $vid = Utilities::getInput('vid', 'GET', 'string');
            /**
             * Filter to restore addon version
             *
             * @param string File id
             * @param string Version id
             *
             * @return string Version
             *
             * @internal
             */
            $version = apply_filters('wpfd_addon_restore_version', $id_file, $vid, $categoryFrom);
            $this->exitStatus($version);
        } else {
            Application::getInstance('Wpfd');
            $model = $this->getModel();
            $file = $model->getFile($id_file);
            $version = $model->getVersion($vid);

            if ($version) {
                $model->updateFile($id_file, array(
                    'title' => $file['title'],
                    'file' => $version['file'],
                    'ext' => $version['ext'],
                    'size' => $version['size'],
                    'version' => $version['version'],
                    'remote_url' => $version['remote_url']
                ));

                $model->deleteVersion($vid);
                // Reindex new version file
                Application::getInstance('Wpfd');
                /* @var WpfdModelGeneratepreview $generatePreviewModel */
                $generatePreviewModel = $this->getModel('generatepreview');
                $ftsModel = $this->getModel('fts');
                $ftsModel->wpfdPostReindex($id_file);

                $generatePreviewModel->removeFileFromQueue($id_file);
                $generatePreviewModel->addFileToQueue($id_file);
                $this->exitStatus(true);
            }
            $this->exitStatus(false);
        }
    }

    /**
     * Delete file version
     *
     * @return void
     */
    public function deleteVersion()
    {

        $idCategory = Utilities::getInt('catid');
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $idCategory);
        if ($categoryFrom === 'googleDrive') {
            $id_file = Utilities::getInput('id_file', 'GET', 'none');
            $vid = Utilities::getInput('vid', 'GET', 'none');
            /**
             * Filter to delete a cloud version
             *
             * @param integer Term id
             * @param string  File id
             * @param string  Version id
             *
             * @internal
             *
             * @return string
             */
            if (apply_filters('wpfdAddonDeleteVersion', $idCategory, $id_file, $vid)) {
                $this->exitStatus(true, array());
            } else {
                $this->exitStatus('error validating');
            }
        } else {
            $vid = Utilities::getInt('vid');
            Application::getInstance('Wpfd');
            $model = $this->getModel();
            $id_file = Utilities::getInput('id_file', 'GET', 'none');
            $file = $model->getFile($id_file);
            $version = $model->getVersion($vid);
            $file_dir = WpfdBase::getFilesPath($file['catid']) . '/' . $version['file'];
            $result = (bool)$model->deleteVersion($vid);
            if ($result) {
                if (file_exists($file_dir)) {
                    unlink($file_dir);
                }
            }
            $this->exitStatus($result);
        }
    }

    /**
     * Save file
     *
     * @return void
     */
    public function save()
    {
        Application::getInstance('Wpfd');
        $model = $this->getModel();
        $modelCat = $this->getModel('category');
        $modelNotify = $this->getModel('notification');
        $configNotify = $modelNotify->getNotificationsConfig();
        $modelConfig = $this->getModel('config');
        $config = $modelConfig->getConfig();
        $dateFormat = $config['date_format'];
        //file multi category
        $file_multi_category_input = Utilities::getInput('file_multi_category', 'POST', 'none');
        if (strstr($file_multi_category_input, ',')) {
            $file_multi_category = explode(',', $file_multi_category_input);
        } else {
            $file_multi_category = array($file_multi_category_input);
        }

        $file_multi_category_old_input = Utilities::getInput('file_multi_category_old', 'POST', 'none');
        if (strstr($file_multi_category_old_input, ',')) {
            $file_multi_category_old = explode(',', $file_multi_category_old_input);
        } else {
            $file_multi_category_old = array($file_multi_category_old_input);
        }

        $id_file = Utilities::getInt('id');
        $form = new Form();

        if (!$form->load('file')) {
            $this->exitStatus('error');
        }
        $data = $form->sanitize();
        // Publish date only for local file
        if ($data['publish'] !== '' && $data['publish'] !== '0000-00-00 00:00:00') {
            $data['publish'] = WpfdBase::validateDate($data['publish'], $dateFormat);
        }
        // Expiration date only for local file
        if ($data['expiration'] !== '' && $data['expiration'] !== '0000-00-00 00:00:00') {
            $data['expiration'] = WpfdBase::validateDate($data['expiration'], $dateFormat);
        }
        $data['file_multi_category'] = $file_multi_category;
        if (!empty($file_multi_category)) {
            $data['file_multi_category_old'] = implode(',', $file_multi_category);
        }

        $idCategory = Utilities::getInt('idCategory');
        $category = $modelCat->getCategory($idCategory);
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $idCategory);
        if (in_array($categoryFrom, wpfd_get_support_cloud())) {
            $fileId = Utilities::getInput('id', 'GET', 'none');
            $data['id'] = $fileId;
            /**
             * Filters to save addon file info
             *
             * @param array File data
             *
             * @internal
             *
             * @return boolean
             */
            if (apply_filters('wpfd_addon_save_file_info', $data, $categoryFrom, $idCategory)) {
                $this->saveCatRefToFiles(
                    $modelCat,
                    $file_multi_category_old,
                    $file_multi_category,
                    $fileId,
                    $idCategory
                );
                $this->sendEmail(
                    'edited',
                    null,
                    $category->params['category_own'],
                    $configNotify,
                    $category->name,
                    $data['title']
                );
                $this->exitStatus(true);
            } else {
                $this->exitStatus('error saving');
            }
        } else {
            $data['id'] = $id_file;

            $data['description'] = Utilities::getInput('description', 'POST', 'none');
            if (!$model->save($data)) {
                $this->exitStatus('error saving');
            }
            $this->saveCatRefToFiles($modelCat, $file_multi_category_old, $file_multi_category, $id_file, $idCategory);
            $file = $model->getFile($data['id']);

            Application::getInstance('Wpfd');
            $ftsModel = $this->getModel('fts');
            $ftsModel->wpfdPostReindex($data['id']);
            $this->sendEmail(
                'edited',
                $file['post_author'],
                $category->params['category_own'],
                $configNotify,
                $category->name,
                $file['post_title']
            );
            $this->exitStatus(true);
        }
    }

    /**
     * Save multiple category to file meta
     *
     * @param mixed   $modelCat                Category model
     * @param array   $file_multi_category_old Old category list
     * @param array   $file_multi_category     Category list
     * @param string  $id_file                 File id
     * @param integer $idCategory              Category id
     *
     * @return void
     */
    public function saveCatRefToFiles($modelCat, $file_multi_category_old, $file_multi_category, $id_file, $idCategory)
    {
        $lst_catRef_del = array();
        if ((!empty($file_multi_category_old) && $file_multi_category) && $file_multi_category_old) {
            $lst_catRef_del = array_diff($file_multi_category_old, $file_multi_category);
        }
        if (!empty($file_multi_category) && $file_multi_category) {
            foreach ($file_multi_category as $value) {
                if (trim($value) !== '') {
                    $modelCat->saveRefToFiles($value, $id_file, $idCategory);
                }
            }
            if (!empty($lst_catRef_del) && $lst_catRef_del) {
                foreach ($lst_catRef_del as $value) {
                    if (trim($value) !== '') {
                        $modelCat->deleteRefToFiles($value, $id_file, $idCategory);
                    }
                }
            }
        } elseif (!empty($file_multi_category_old)) {
            foreach ($file_multi_category_old as $value) {
                if (trim($value) !== '') {
                    $modelCat->deleteRefToFiles($value, $id_file, $idCategory);
                }
            }
        }
    }

    /**
     * Delete file
     *
     * @return void
     */
    public function delete()
    {
        $idCategory = Utilities::getInt('id_category');
        $catIdFileRef = Utilities::getInt('catid_file_ref');
        Application::getInstance('Wpfd');
        $modelCat = $this->getModel('category');
        $category = $modelCat->getCategory($idCategory);
        $modelNotify = $this->getModel('notification');
        $configNotify = $modelNotify->getNotificationsConfig();
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         *
         * @ignore
         */
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $idCategory);

        if (in_array($categoryFrom, wpfd_get_support_cloud())) {
            $id_file = Utilities::getInput('id_file', 'GET', 'string');
            /**
             * Filter to get addon file info
             *
             * @param string  File id
             * @param integer Category term id
             * @param string  Category from
             *
             * @internal
             *
             * @return array
             */
            $file = apply_filters('wpfd_addon_get_file_info', $id_file, $idCategory, $categoryFrom);
            $this->sendEmail(
                'delete',
                null,
                $category->params['category_own'],
                $configNotify,
                $category->name,
                $file['title']
            );
            /**
             * Filter delete addon files
             *
             * @param integer Category id
             * @param string  File id
             *
             * @internal
             *
             * @return boolean
             */
            if (apply_filters('wpfd_addon_delete_file', $idCategory, $id_file, $categoryFrom)) {
                /**
                 * Action fire after a file deleted
                 *
                 * @param array   Deleted file info
                 * @param WP_Term Category the file was deleted from
                 * @param array   Additional information
                 */
                do_action('wpfd_file_deleted', $file, $category, array('source' => $categoryFrom));
                $this->exitStatus(true);
            } else {
                $this->exitStatus(false);
            }
        } else {
            $id_file = Utilities::getInt('id_file');
            $model = $this->getModel();
            $versions = $model->getVersions($id_file, $idCategory);
            $file = $model->getFile($id_file);

            if (!empty($versions)) {
                foreach ($versions as $key => $value) {
                    $version = $model->getVersion($value['meta_id']);
                    $file_dir = WpfdBase::getFilesPath($file['catid']) . '/' . $version['file'];
                    $result = (bool)$model->deleteVersion($value['meta_id']);
                    if ($result) {
                        if (file_exists($file_dir)) {
                            unlink($file_dir);
                        }
                    }
                }
            }
            if (!empty($file)) {
                if ($catIdFileRef === $idCategory) {
                    $file_multi_category = null;
                    if (isset($file['file_multi_category'])) {
                        $file_multi_category = $file['file_multi_category'];
                    }
                    if ($file_multi_category) {
                        foreach ($file_multi_category as $value) {
                            $modelCat->deleteRefToFiles($value, $id_file, $idCategory);
                        }
                    }
                    if ($model->delete($id_file)) {
                        $file_dir = WpfdBase::getFilesPath($file['catid']);
                        if (file_exists($file_dir . $file['file'])) {
                            unlink($file_dir . $file['file']);
                            $this->sendEmail(
                                'delete',
                                $file['post_author'],
                                $category->params['category_own'],
                                $configNotify,
                                $category->name,
                                $file['post_title']
                            );
                            // Full Text Search Index When Delete File
                            $ftsModel = Model::getInstance('fts');
                            $ftsModel->removeIndexRecordForPost($id_file);
                            // Delete preview file if exists
                            /* @var WpfdModelGeneratepreview $generatePreviewModel */
                            $generatePreviewModel = Model::getInstance('generatepreview');
                            $generatePreviewModel->removeFileFromQueue($id_file);
                            /**
                             * Action fire after a file deleted
                             *
                             * @param array   Deleted file info
                             * @param WP_Term Category the file was deleted from
                             * @param array   Additional information
                             *
                             * @ignore
                             */
                            do_action('wpfd_file_deleted', $file, $category, array('source' => 'local'));
                            $this->exitStatus(true);
                        }
                    }
                } else {
                    $modelCat->deleteRefToFiles($idCategory, $id_file, $catIdFileRef);
                    $metadata = get_post_meta($file['ID'], '_wpfd_file_metadata', true);
                    if (isset($metadata['file_multi_category'])) {
                        $file_multi_category = $metadata['file_multi_category'];
                        foreach ($file_multi_category as $key => $val) {
                            if ($idCategory === (int)$val) {
                                unset($file_multi_category[$key]);
                            }
                        }
                        $metadata['file_multi_category'] = $file_multi_category;
                        $metadata['file_multi_category_old'] = implode(',', $file_multi_category);
                        update_post_meta($file['ID'], '_wpfd_file_metadata', $metadata);
                        $this->exitStatus(true);
                    }
                }
            }
        }
        $this->exitStatus('error while deleting');
    }

    /**
     * Send email
     *
     * @param string       $action       Action to send email
     * @param integer|null $user_id      Current user id
     * @param string       $cat_userid   Category owner id
     * @param array        $configNotify Email configurations
     * @param string       $cat_name     Category had action
     * @param string       $file_title   File name in action
     *
     * @return void
     */
    public function sendEmail($action, $user_id, $cat_userid, $configNotify, $cat_name, $file_title)
    {
        $send_mail_active = array();
        $cat_user_id[] = $cat_userid;
        $list_superAdmin = WpfdHelperFiles::getListIDSuperAdmin();
        if ((int)$configNotify['notify_file_owner'] === 1 && $user_id !== null) {
            $user = get_userdata($user_id)->data;
            array_push($send_mail_active, $user->user_email);
            WpfdHelperFiles::sendMail($action, $user, $cat_name, get_site_url(), $file_title);
        }
        if ((int)$configNotify['notify_category_owner'] === 1) {
            foreach ($cat_user_id as $item) {
                $user = get_userdata($item)->data;
                if (!in_array($user->user_email, $send_mail_active)) {
                    array_push($send_mail_active, $user->user_email);
                    WpfdHelperFiles::sendMail($action, $user, $cat_name, get_site_url(), $file_title);
                }
            }
        }
        if ($configNotify['notify_add_event_email'] !== '') {
            $emails = explode(',', $configNotify['notify_add_event_email']);
            foreach ($emails as $item) {
                $obj_user = new stdClass;
                $obj_user->display_name = '';
                $obj_user->user_email = $item;
                if (!in_array($item, $send_mail_active)) {
                    array_push($send_mail_active, $item);
                    WpfdHelperFiles::sendMail($action, $obj_user, $cat_name, get_site_url(), $file_title);
                }
            }
        }
        if ((int)$configNotify['notify_super_admin'] === 1) {
            foreach ($list_superAdmin as $items) {
                $user = get_userdata($items)->data;
                if (!in_array($user->user_email, $send_mail_active)) {
                    array_push($send_mail_active, $user->user_email);
                    WpfdHelperFiles::sendMail($action, $user, $cat_name, get_site_url(), $file_title);
                }
            }
        }
    }

    /**
     * Call file shortcode
     *
     * @throws Exception Return if error
     *
     * @return void
     */
    public function callFileShortcode()
    {
        $app = Application::getInstance('Wpfd');
        $id_file     = Utilities::getInput('file_id', 'GET', 'none');
        $id_category = Utilities::getInt('category_id');
        $path_wpfdhelper = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'helpers';
        $path_wpfdhelper .= DIRECTORY_SEPARATOR . 'WpfdHelperShortcodes.php';
        require_once $path_wpfdhelper;

        $helperShortcode = new WpfdHelperShortcodes();
        $singleFile = $helperShortcode->callSingleFile($id_file, $id_category);
        wp_send_json(array(
            'success' => true,
            'data'    => $singleFile
        ));
        die();
    }
}
