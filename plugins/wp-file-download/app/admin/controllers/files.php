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
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerFiles
 */
class WpfdControllerFiles extends Controller
{

    /**
     * Default allow extension
     *
     * @var array
     */
    private $allowed_ext = array(
        'jpg',
        'jpeg',
        'png',
        'gif',
        'pdf',
        'doc',
        'docx',
        'xls',
        'xlsx',
        'zip',
        'tar',
        'rar',
        'odt',
        'ppt',
        'pps',
        'txt'
    );

    /**
     * Search files
     *
     * @return void
     */
    public function search()
    {
        $s                 = Utilities::getInput('s', 'POST', 'string');
        $id_category       = Utilities::getInput('cid', 'POST', 'int');
        $orderCol          = Utilities::getInput('orderCol', 'GET', 'none');
        $orderDir          = Utilities::getInput('orderDir', 'GET', 'none');
        $ordering          = $orderCol !== null ? $orderCol : 'title';
        $orderingDir       = $orderDir !== null ? $orderDir : 'ASC';

        Application::getInstance('Wpfd');
        $model             = $this->getModel();
        $modelConfig       = $this->getModel('config');
        $modelCats           = $this->getModel('categories');
        $view              = $this->loadView();

        $files             = $model->searchfile($s, $id_category, $ordering, $orderingDir);
        $view->ordering    = $ordering;
        $view->orderingdir = $orderingDir;
        $view->files       = $files;

        $params      = $modelConfig->getConfig();
        if ($params['admin_theme'] !== 'table') {
            $tpl = trim($params['admin_theme']);
            // Loading categories
            $categories   = $modelCats->getSubCategories($id_category);
            $categories = $modelCats->extractOwnCategories($categories);
            $view->categories = array();
            foreach ($categories as $category) {
                $catItem = $category;
                $categoryType = apply_filters('wpfdAddonCategoryFrom', $catItem->term_id);
                if (in_array($categoryType, wpfd_get_support_cloud())) {
                    $catItem->type = $categoryType;
                } else {
                    $catItem->type = 'wordpress';
                }

                $view->categories[] = $catItem;
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape inside function
            echo $view->loadTemplate($tpl);
        } else {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escape inside function
            echo $view->loadTemplate();
        }
        die();
    }

    /**
     * Upload a file
     *
     * @return void
     */
    public function upload()
    {
        $id_category = Utilities::getInt('id_category') ?
            Utilities::getInt('id_category') :
            Utilities::getInt('id_category', 'POST');
        // Check if category exists
        if (!term_exists($id_category, 'wpfd-category')) {
            $this->exitStatus(false, array('code' => 22, 'message' => esc_html__('This category is no longer exists. It may be deleted!', 'wpfd')));
        }
        $modelCat    = $this->getModel('category');
        $category    = $modelCat->getCategory($id_category);

        if ($id_category <= 0) {
            $this->exitStatus(esc_html__('Wrong Category', 'wpfd'));
        }

        $configModel  = $this->getModel('config');
        $modalNotify  = $this->getModel('notification');
        $configNotify = $modalNotify->getNotificationsConfig();
        $allowed      = $configModel->getAllowedExt();

        if (!empty($allowed)) {
            $this->allowed_ext = $allowed;
        }
        /**
         * Filter to check category source
         *
         * @param integer Term id
         *
         * @return string
         *
         * @internal
         */
        $placeUpload = apply_filters('wpfdAddonCategoryFrom', $id_category);

        // todo: Replace code for external source later

        //todo: vérifier les erreurs de création de fichier
        $file_dir = WpfdBase::getFilesPath($id_category);
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
        // Delete chunks of cancelled files
        $deleteChunks = Utilities::getInput('deleteChunks', 'POST', 'none');
        if ($deleteChunks) {
            $this->rrmdir($file_dir . md5($deleteChunks));
            $this->exitStatus(true, array('deletedChunks' => $deleteChunks));
        }
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $resumableIdentifier  = Utilities::getInput('resumableIdentifier', '', 'none');
            $resumableFilename    = Utilities::getInput('resumableFilename', '', 'none');
            $resumableChunkNumber = Utilities::getInt('resumableChunkNumber', '');
            $temp_dir             = $file_dir . md5($resumableIdentifier);
            $filename             = md5($resumableFilename);
            $chunk_file           = $temp_dir . '/' . $filename . '.part' . $resumableChunkNumber;

            if (file_exists($chunk_file)) {
                header('HTTP/1.0 200');
            } else {
                // File's chunk not yet uploaded. Upload it!
                header('HTTP/1.0 204');
            }
        }
        // loop through files and move the chunks to a temporarily created directory
        if (!empty($_FILES)) {
            foreach ($_FILES as $file_upload) {
                // check the error status
                if ((int) $file_upload['error'] !== 0) {
                    header('HTTP/1.0 400 Bad Request');
                    continue;
                }
                // init the destination file (format <filename.ext>.part<#chunk>
                // the file is stored in a temporary directory
                $resumableIdentifier  = html_entity_decode(Utilities::getInput('resumableIdentifier', 'POST', 'none'));
                $resumableFilename    = html_entity_decode(Utilities::getInput('resumableFilename', 'POST', 'none'));
                $resumableChunkNumber = Utilities::getInt('resumableChunkNumber', 'POST');
                $resumableTotalSize   = Utilities::getInt('resumableTotalSize', 'POST');
                $resumableTotalChunks = Utilities::getInt('resumableTotalChunks', 'POST');
                $temp_dir             = $file_dir . md5($resumableIdentifier);
                $filename             = md5($resumableFilename);
                $dest_file            = $temp_dir . '/' . $filename . '.part' . $resumableChunkNumber;
                // create the temporary directory
                if (!is_dir($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }
                $ext     = strtolower(pathinfo($resumableFilename, PATHINFO_EXTENSION));
                $newname = uniqid() . '.' . $ext;
                $model   = $this->getModel();
                // move the temporary file
                if (!move_uploaded_file($file_upload['tmp_name'], $dest_file)) {
                    $this->exitStatus(esc_html__('Cannot move uploaded file', 'wpfd') . ' ' . $file_upload['name']);
                } else {
                    // check if all the parts present, and create the final destination file
                    $joinFiles = $this->createFileFromChunks(
                        $temp_dir,
                        $file_dir,
                        $filename,
                        $newname,
                        $resumableTotalSize,
                        $resumableTotalChunks
                    );
                    if ($joinFiles === false) {
                        $this->exitStatus('Error saving file ' . $file_upload['name']);
                    } elseif ($joinFiles === true) {
                        if (!WpfdHelperFile::checkMimeType($file_dir . $newname)) {
                            unlink($file_dir . $newname);
                            $this->exitStatus(esc_html__('The file type (mime type) is not valid', 'wpfd'));
                        }
                        if (in_array($placeUpload, wpfd_get_support_cloud())) {
                            $file_title   = pathinfo($resumableFilename, PATHINFO_FILENAME);
                            $file_current = $file_dir . $newname;
                            $item         = array(
                                'title' => $file_title,
                                'ext'   => $ext,
                                'size'  => filesize($file_dir . $newname)
                            );
                            /**
                             * Action upload addon file
                             *
                             * @param array   File
                             * @param string  File name
                             * @param integer Category id
                             *
                             * @internal
                             */
                            do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $placeUpload);
                            $this->sendEmail(
                                get_current_user_id(),
                                $category->params['category_own'],
                                $configNotify,
                                $category->name,
                                $file_title
                            );
                            unlink($file_dir . $newname);
                            $this->exitStatus(true);
                        } else {
                            //Insert new image into databse when success
                            $file_ext = pathinfo($resumableFilename, PATHINFO_EXTENSION);
                            $file_title = str_replace('.' . $file_ext, '', $resumableFilename);
                            $file_title = str_replace('|', '/', $file_title);
                            $id_file = $model->addFile(array(
                                'title' => $file_title,
                                'id_category' => $id_category,
                                'file'        => $newname,
                                'ext'         => $ext,
                                'size'        => filesize($file_dir . $newname),
                            ));
                            if (!$id_file) {
                                unlink($file_dir . $newname);
                                $this->exitStatus(esc_html__('Can\'t save to database', 'wpfd'));
                            }
                            $this->sendEmail(
                                get_current_user_id(),
                                $category->params['category_own'],
                                $configNotify,
                                $category->name,
                                $file_title
                            );
                            // Full Text Search Index When New File Uploaded
                            Application::getInstance('Wpfd');
                            $ftsModel = Model::getInstance('fts');
                            /* @var WpfdModelGeneratepreview $generatePreviewModel */
                            $generatePreviewModel = Model::getInstance('generatepreview');
                            $ftsModel->wpfdPostReindex($id_file);
                            $generatePreviewModel->addFileToQueue($id_file);
                        }
                        $this->exitStatus(true, array('id_file' => $id_file, 'name' => $newname));
                    }
                    $this->exitStatus(true, array());
                }
            }
        }
        $this->exitStatus(esc_html__('Error while uploading', 'wpfd'));
    }

    /**
     * Send email
     *
     * @param integer|null $user_id      Current user id
     * @param string       $cat_userid   Category owner id
     * @param array        $configNotify Email configurations
     * @param string       $cat_name     Category had action
     * @param string       $file_title   File name in action
     *
     * @return void
     */
    public function sendEmail($user_id, $cat_userid, $configNotify, $cat_name, $file_title)
    {
        $send_mail_active = array();
        $cat_user_id[]    = $cat_userid;
        $list_superAdmin  = WpfdHelperFiles::getListIDSuperAdmin();
        if ((int) $configNotify['notify_file_owner'] === 1 && $user_id !== null) {
            $user = get_userdata($user_id);
            array_push($send_mail_active, $user->data->user_email);
            WpfdHelperFiles::sendMail('added', $user->data, $cat_name, get_site_url(), $file_title);
        }
        if ((int) $configNotify['notify_category_owner'] === 1) {
            foreach ($cat_user_id as $item) {
                if ($item !== '') {
                    $user = get_userdata($item);
                    if (!is_wp_error($user) && !in_array($user->data->user_email, $send_mail_active)) {
                        array_push($send_mail_active, $user->data->user_email);
                        WpfdHelperFiles::sendMail('added', $user->data, $cat_name, get_site_url(), $file_title);
                    }
                }
            }
        }

        if ($configNotify['notify_add_event_email'] !== '') {
            if (strpos($configNotify['notify_add_event_email'], ',')) {
                $emails = explode(',', $configNotify['notify_add_event_email']);
            } else {
                $emails = array($configNotify['notify_add_event_email']);
            }

            foreach ($emails as $item) {
                $obj_user               = new stdClass;
                $obj_user->display_name = '';
                $obj_user->user_email   = $item;
                if (!in_array($item, $send_mail_active)) {
                    array_push($send_mail_active, $item);
                    WpfdHelperFiles::sendMail('added', $obj_user, $cat_name, get_site_url(), $file_title);
                }
            }
        }
        if ((int) $configNotify['notify_super_admin'] === 1) {
            foreach ($list_superAdmin as $items) {
                $user = get_userdata($items);
                if (!in_array($user->data->user_email, $send_mail_active)) {
                    array_push($send_mail_active, $user->data->user_email);
                    WpfdHelperFiles::sendMail('added', $user->data, $cat_name, get_site_url(), $file_title);
                }
            }
        }
    }

    /**
     * Copy file
     *
     * @return void
     */
    public function copyfile()
    {
        global $wp_filesystem;

        Application::getInstance('Wpfd');
        $modelFile = $this->getModel('file');
        $model     = $this->getModel();

        $id_category        = Utilities::getInt('id_category', 'GET');
        $active_category_id = Utilities::getInt('active_category', 'GET');
        $id_file            = Utilities::getInput('id_file', 'GET', 'string');
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
        $targetCategoryName = apply_filters('wpfdAddonCategoryFrom', $id_category);
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
        $activeCategoryName = apply_filters('wpfdAddonCategoryFrom', $active_category_id);
        $file               = $modelFile->getFile($id_file);
        $fileMetaData = get_post_meta($id_file, '_wpfd_file_metadata', true);

        if (!defined('WPFDA_VERSION')) {
            $targetCategoryName = false;
            $activeCategoryName = false;
        }

        if ($activeCategoryName === false && $targetCategoryName === false) {
            if ((int) $file['catid'] !== $id_category) {
                // Copy file metadata
                if ($fileMetaData['remote_url']) {
                    $id_file_new = $model->addFile(array(
                        'title'       => $file['title'],
                        'id_category' => (int) $id_category,
                        'file'        => $fileMetaData['file'],
                        'ext'         => $file['ext'],
                        'size'        => $file['size']
                    ), true);
                } else {
                    $newname     = uniqid() . '.' . $file['ext'];
                    $id_file_new = $model->addFile(array(
                        'title'       => $file['title'],
                        'id_category' => (int) $id_category,
                        'file'        => $newname,
                        'ext'         => $file['ext'],
                        'size'        => $file['size']
                    ));

                    if ($id_file_new) {
                        $file_current = WpfdBase::getFilesPath($active_category_id) . $file['file'];
                        $file_dir     = WpfdBase::getFilesPath($id_category);
                        $file_dest    = $file_dir . $newname;

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

                        if (is_file($file_current)) {
                            if (!copy($file_current, $file_dest)) {
                                $this->exitStatus(esc_html__('Error: Can\'t copy file.', 'wpfd'));
                            }
                            Application::getInstance('Wpfd');
                            $ftsModel = Model::getInstance('fts');
                            $generatePreviewModel = Model::getInstance('generatepreview');
                            $generatePreviewModel->copyPreviewFile($file->ID, $id_file_new);
                            $ftsModel->wpfdPostReindex($id_file_new);
                        }
                    }
                    $fileMetaData['file'] = $newname;
                }

                if ($id_file_new) {
                    // Reset some data
                    $fileMetaData['file_multi_category'] = array();
                    $fileMetaData['file_multi_category_old'] = '';
                    $fileMetaData['hits'] = 0;
                    // Copy to new file metadata
                    update_post_meta($id_file_new, '_wpfd_file_metadata', $fileMetaData);

                    // Update new file description
                    wp_update_post(array(
                        'ID' => $id_file_new,
                        'post_excerpt' => $file['description']
                    ));
                }
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === false) {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $tmpFile = fopen($file_dir . 'index.html', 'w');
                    fwrite($tmpFile, $data);
                    fclose($tmpFile);
                    $data = 'deny from all';
                    $tmpFile = fopen($file_dir . '.htaccess', 'w');
                    fwrite($tmpFile, $data);
                    fclose($tmpFile);
                }

                $newname = uniqid() . '.' . $file->ext;

//                file_put_contents($file_dir . $newname, $file->datas);
                rename($tmpFilePath, $file_dir . $newname);
                Application::getInstance('Wpfd');

                $model       = $this->getModel();
                $id_file_new = $model->addFile(array(
                    'title'       => $file->title,
                    'id_category' => (int) $id_category,
                    'file'        => $newname,
                    'ext'         => $file->ext,
                    'size'        => $file->size
                ));

                if ($id_file_new) {
                    $this->copyMetaGoogleDriveToLocal($id_file_new, $file);
                    // Index new file
                    Application::getInstance('Wpfd');
                    $ftsModel = Model::getInstance('fts');
                    $ftsModel->wpfdPostReindex($id_file_new);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'googleDrive') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload addon file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);
            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'googleDrive', null);
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'googleDrive') {
            /**
             * Action copy google drive file
             *
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonCopyGoogleGoogle', $id_file, $id_category);
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === false) {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest     = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $file = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($file, $data);
                    fclose($file);
                    $data = 'deny from all';
                    $file = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($file, $data);
                    fclose($file);
                }

                $newname = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

                ob_start();
                header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: public');

                // header('Content-Length: ' . (int)$file['size']);
                // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- print file content
                echo readfile($tem);
                unlink($tem);
                $data = ob_get_clean();
                file_put_contents($catpath_dest . $newname, $data);

                $model    = $this->getModel();
                $new_file_id = $model->addFile(array(
                    'title'       => pathinfo($file['name'], PATHINFO_FILENAME),
                    'id_category' => $id_category,
                    'file'        => $newname,
                    'ext'         => pathinfo($file['name'], PATHINFO_EXTENSION),
                    'size'        => $file['size']
                ));
                if ($new_file_id) {
                    // Add file meta data
                    $this->copyMetaDropboxToLocal($active_category_id, $file, $new_file_id);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'dropbox') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload addon file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'dropbox', $id_category);
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'dropbox') {
            /**
             * Action copy dropbox file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonCopyDropboxDropbox', $id_file, $id_category);
            // Get source file version, description
            $version = '';
            $description = '';
            $fileInfos = WpfdAddonHelper::getDropboxFileInfos();
            if (!empty($fileInfos)) {
                if (isset($fileInfos[$active_category_id]) && isset($fileInfos[$active_category_id][$id_file])) {
                    $version = $fileInfos[$active_category_id][$id_file]['version'];
                    $description = $fileInfos[$active_category_id][$id_file]['description'];
                }
            }

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $version, $description, 'dropbox', $id_category);
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'dropbox') {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );

            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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


                $newname = uniqid() . '.' . $file->ext;
//                file_put_contents($file_dir . $newname, $file->datas);

                rename($tmpFilePath, $file_dir . $newname);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext
                );
                /**
                 * Action upload dropbox file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'dropbox', $id_category);
            }
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'googleDrive') {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $file = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($file, $data);
                    fclose($file);
                    $data = 'deny from all';
                    $file = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($file, $data);
                    fclose($file);
                }

                $item = array(
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'ext'   => pathinfo($file['name'], PATHINFO_EXTENSION)
                );
                /**
                 * Action upload addon file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $tem, $id_category, $targetCategoryName);
                unlink($tem);

                // Update new file version and description
                list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);

                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'googleDrive', $id_category);
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'onedrive') {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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

                $newname = uniqid() . '.' . $file->ext;
//                file_put_contents($file_dir . $newname, $file->datas);
                rename($tmpFilePath, $file_dir . $newname);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'onedrive', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'googleDrive') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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

                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);

                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload addon file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                unlink($file_current);

                list($version, $description) = $this->getOnedriveVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'googleDrive', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'dropbox') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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
                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);

                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext
                );
                /**
                 * Action upload dropbox file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                unlink($file_current);

                // Update version and description
                list($version, $description) = $this->getOnedriveVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'dropbox', $id_category);
            }
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'onedrive') {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $file = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($file, $data);
                    fclose($file);
                    $data = 'deny from all';
                    $file = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($file, $data);
                    fclose($file);
                }

                $item = array(
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'ext'   => pathinfo($file['name'], PATHINFO_EXTENSION),
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $tem, $id_category, $targetCategoryName);
                unlink($tem);

                // Update version and description
                list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === false) {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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


                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);
                Application::getInstance('Wpfd');

                $model       = $this->getModel();
                $id_file_new = $model->addFile(array(
                    'title'       => $file->title,
                    'id_category' => (int) $id_category,
                    'file'        => $newname,
                    'ext'         => $file->ext,
                    'size'        => $file->size
                ));

                if ($id_file_new) {
                    $this->copyMetaOnedriveToLocal($active_category_id, $file, $id_file_new);
                    Application::getInstance('Wpfd');
                    $ftsModel = Model::getInstance('fts');
                    $ftsModel->wpfdPostReindex($id_file_new);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'onedrive') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload onedrive file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);
            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'onedrive', $id_category);
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'onedrive') {
            /**
             * Action copy onedrive file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonCopyOneDrive', $id_file, $id_category);

            // Get source file version, description
            $version = '';
            $description = '';
            $fileInfos = WpfdAddonHelper::getOneDriveFileInfos();
            if (!empty($fileInfos)) {
                if (isset($fileInfos[$active_category_id]) && isset($fileInfos[$active_category_id][$id_file])) {
                    $version = $fileInfos[$active_category_id][$id_file]['version'];
                    $description = $fileInfos[$active_category_id][$id_file]['description'];
                }
            }

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive', $id_category);
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'onedrive_business') {
            /**
             * Action copy onedrive business file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonCopyOneDriveBusiness', $id_file, $id_category);
            // Get source file version, description
            $version = '';
            $description = '';
            $fileInfos = WpfdAddonHelper::getOneDriveBusinessFileInfos();
            if (!empty($fileInfos)) {
                if (isset($fileInfos[$active_category_id]) && isset($fileInfos[$active_category_id][$id_file])) {
                    $version = $fileInfos[$active_category_id][$id_file]['version'];
                    $description = $fileInfos[$active_category_id][$id_file]['description'];
                }
            }

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive_business', $id_category);
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'googleDrive') {
            /**
             * Filters to get onedrive business file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);

            if (!is_wp_error($file)) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;

                file_put_contents($file_dir . $newname, $file->datas);

                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload addon file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                unlink($file_current);

                list($version, $description) = $this->getOnedriveBusinessVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'googleDrive', $id_category);
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'onedrive_business') {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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

                $newname = uniqid() . '.' . $file->ext;
//                file_put_contents($file_dir . $newname, $file->datas);
                rename($tmpFilePath, $file_dir . $newname);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'onedrive_business', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'onedrive') {
            /**
             * Filters to get onedrive business file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);
                $item = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_dir . $newname, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'onedrive', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'onedrive_business') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);
                $file_current = $file_dir . $newname;
                $item = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                unlink($file_current);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'onedrive_business', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'dropbox') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $file = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }
                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);

                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext
                );
                /**
                 * Action upload dropbox file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                unlink($file_current);

                // Update version and description
                list($version, $description) = $this->getOnedriveBusinessVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'dropbox', $id_category);
            }
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'onedrive_business') {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $item = array(
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'ext'   => pathinfo($file['name'], PATHINFO_EXTENSION),
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive business file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $tem, $id_category, $targetCategoryName);
                unlink($tem);

                // Update version and description
                list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive_business', $id_category);
            }
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === false) {
            /**
             * Filters to get onedrive business file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);
                Application::getInstance('Wpfd');

                $model       = $this->getModel();
                $id_file_new = $model->addFile(array(
                    'title'       => $file->title,
                    'id_category' => (int) $id_category,
                    'file'        => $newname,
                    'ext'         => $file->ext,
                    'size'        => $file->size
                ));

                if ($id_file_new) {
                    $this->copyMetaOnedriveToLocal($active_category_id, $file, $id_file_new);
                    Application::getInstance('Wpfd');
                    $ftsModel = Model::getInstance('fts');
                    $ftsModel->wpfdPostReindex($id_file_new);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'onedrive_business') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload onedrive file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);
            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'onedrive_business', $id_category);
        } else {
            $this->exitStatus(esc_html__('Error: Something wrong here', 'wpfd'));
        }
        $this->exitStatus(true);
        exit();
    }

    /**
     * Move file
     *
     * @return void
     */
    public function movefile()
    {
        global $wp_filesystem;

        $id_category        = Utilities::getInt('id_category', 'GET');
        $active_category_id = Utilities::getInt('active_category', 'GET');
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
        $targetCategoryName = apply_filters('wpfdAddonCategoryFrom', $id_category);
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
        $activeCategoryName = apply_filters('wpfdAddonCategoryFrom', $active_category_id);
        $modelFile          = $this->getModel('file');
        $id_file            = Utilities::getInput('id_file', 'GET', 'string');
        $file               = $modelFile->getFile($id_file);

        if (!defined('WPFDA_VERSION')) {
            $targetCategoryName = false;
            $activeCategoryName = false;
        }
        if ($activeCategoryName === false && $targetCategoryName === false) {
            wp_set_post_terms($id_file, $id_category, 'wpfd-category');
            $modelCategory = $this->getModel('category');
            $file          = $modelCategory->checkMoveFileRefToCat($active_category_id, $file, $id_category);

            $file_current = WpfdBase::getFilesPath($active_category_id) . $file['file'];
            $file_dir     = WpfdBase::getFilesPath($id_category);
            $file_dest    = $file_dir . $file['file'];

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

            if (is_file($file_current)) {
                if (!rename($file_current, $file_dest)) {
                    $this->exitStatus(esc_html__('Error: Can not move files!', 'wpfd'));
                }
                Application::getInstance('Wpfd');
                $ftsModel = Model::getInstance('fts');
                $ftsModel->wpfdPostReindex($id_file);
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === false) {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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

                $newname = uniqid() . '.' . $file->ext;
//                file_put_contents($file_dir . $newname, $file->datas);
                rename($tmpFilePath, $file_dir . $newname);
                Application::getInstance('Wpfd');
                $model       = $this->getModel();
                $id_file_new = $model->addFile(array(
                    'title'       => $file->title,
                    'id_category' => (int) $id_category,
                    'file'        => $newname,
                    'ext'         => $file->ext,
                    'size'        => $file->size
                ));

                if ($id_file_new) {
                    // Update version and description
                    $this->updateLocalFileMetaData($id_file_new, $file->version, $file->description, $file->file_tags);
                    Application::getInstance('Wpfd');
                    $ftsModel = Model::getInstance('fts');
                    $ftsModel->wpfdPostReindex($id_file_new);

                    $googleDrive->delete($id_file, WpfdAddonHelper::getGoogleDriveIdByTermId($active_category_id));
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'googleDrive') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload google drive file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);
            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'googleDrive', $id_category);
            if ($modelFile->delete($id_file)) {
                unlink($file_current);
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'googleDrive') {
            /**
             * Action move google drive file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonMoveGoogleGoogle', $id_file, $id_category);
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === false) {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $file = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($file, $data);
                    fclose($file);
                    $data = 'deny from all';
                    $file = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($file, $data);
                    fclose($file);
                }

                $newname = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

                rename($tem, $catpath_dest . $newname);
                Application::getInstance('Wpfd');
                $model    = $this->getModel();
                $new_file = $model->addFile(array(
                    'title'       => pathinfo($file['name'], PATHINFO_FILENAME),
                    'id_category' => $id_category,
                    'file'        => $newname,
                    'ext'         => pathinfo($file['name'], PATHINFO_EXTENSION),
                    'size'        => $file['size']
                ));

                if ($new_file) {
                    list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);
                    $this->updateLocalFileMetaData($new_file, $version, $description);
                    $dropbox = new WpfdAddonDropbox();
                    $dropbox->deleteFileDropbox($id_file);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'dropbox') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload dropbox file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);
            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'dropbox', $id_category);
            $modelFile->delete($id_file);
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'dropbox') {
            /**
             * Action move dropbox file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonMoveDropboxDropbox', $id_file, $id_category);

            // Move dropbox meta
            $this->moveDropboxMeta($active_category_id, $id_file, $id_category);
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'dropbox') {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );

            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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


                $newname = uniqid() . '.' . $file->ext;
//                file_put_contents($file_dir . $newname, $file->datas);
                rename($tmpFilePath, $file_dir . $newname);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext
                );
                /**
                 * Action upload dropbox file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'dropbox', $id_category);
                $googleDrive->delete($id_file, WpfdAddonHelper::getGoogleDriveIdByTermId($active_category_id));
            }
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'googleDrive') {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $file = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($file, $data);
                    fclose($file);
                    $data = 'deny from all';
                    $file = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($file, $data);
                    fclose($file);
                }

                $newname = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);


                $file_current = $catpath_dest . $newname;
                rename($tem, $file_current);
                $item = array(
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'ext'   => pathinfo($file['name'], PATHINFO_EXTENSION)
                );
                /**
                 * Action upload google drive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'googleDrive', $id_category);
                unlink($file_current);

                $dropbox = new WpfdAddonDropbox();
                $dropbox->deleteFileDropbox($id_file);
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'onedrive') {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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


                $newname = uniqid() . '.' . $file->ext;
//                file_put_contents($file_dir . $newname, $file->datas);
                rename($tmpFilePath, $file_dir . $newname);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, 'onedrive', $id_category);
                $googleDrive->delete($id_file, WpfdAddonHelper::getGoogleDriveIdByTermId($active_category_id));
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'googleDrive') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );

                /**
                 * Filter to delete file
                 *
                 * @param integer Category id
                 * @param string  File id
                 *
                 * @internal
                 *
                 * @ignore
                 */
                if (apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName)) {
                    /**
                     * Action upload google drive file
                     *
                     * @param array   File
                     * @param string  File name
                     * @param integer Category id
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                    list($version, $description) = $this->getOnedriveVersionDescription($active_category_id, $file);
                    /**
                     * Action update version and description after copy
                     *
                     * @param string Version
                     * @param string Description
                     * @param string Category from
                     * @param string Category TermId
                     *
                     * @internal
                     * @ignore
                     */
                    do_action('wpfd_addon_update_version_description', $version, $description, 'googleDrive', $id_category);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'onedrive') {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }
                $newname = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);

                $file_current = $catpath_dest . $newname;
                rename($tem, $file_current);
                $item = array(
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'ext'   => pathinfo($file['name'], PATHINFO_EXTENSION),
                    'size'  => $file->size
                );
                /**
                 * Action upload addon file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive', $id_category);
                unlink($file_current);

                $dropbox = new WpfdAddonDropbox();
                $dropbox->deleteFileDropbox($id_file);
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'dropbox') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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
                $newname = uniqid() . '.' . $file->ext;
                file_put_contents($file_dir . $newname, $file->datas);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext
                );
                /**
                 * Filter to delete addon file
                 *
                 * @param integer Category id
                 * @param string  File id
                 *
                 * @internal
                 *
                 * @ignore
                 */
                if (apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName)) {
                    /**
                     * Action upload dropbox file
                     *
                     * @param array   File
                     * @param string  File name
                     * @param integer Category id
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                    list($version, $description) = $this->getOnedriveVersionDescription($active_category_id, $file);
                    /**
                     * Action update version and description after copy
                     *
                     * @param string Version
                     * @param string Description
                     * @param string Category from
                     * @param string Category TermId
                     *
                     * @internal
                     * @ignore
                     */
                    do_action('wpfd_addon_update_version_description', $version, $description, 'dropbox', $id_category);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === false) {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
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
                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                Application::getInstance('Wpfd');
                $model       = $this->getModel();
                $id_file_new = $model->addFile(array(
                    'title'       => $file->title,
                    'id_category' => (int) $id_category,
                    'file'        => $newname,
                    'ext'         => $file->ext,
                    'size'        => $file->size
                ));
                if ($id_file_new) {
                    list($version, $description) = $this->getOnedriveVersionDescription($active_category_id, $file);
                    $this->updateLocalFileMetaData($id_file_new, $version, $description);
                    /**
                     * Filter to delete addon file
                     *
                     * @param integer Category id
                     * @param string  File id
                     *
                     * @internal
                     *
                     * @ignore
                     */
                    apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'onedrive') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload onedrive file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], 'onedrive', $id_category);
            if ($modelFile->delete($id_file)) {
                unlink($file_current);
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'onedrive') {
            /**
             * Action move onedrive file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonMoveFileOneDriver', $id_file, $id_category);
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'onedrive_business') {
            /**
             * Action copy onedrive business file
             *
             * @param string  File id
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfdAddonMoveOneDriveBusiness', $id_file, $id_category);

            // Get source file version, description
            $version = '';
            $description = '';
            $fileInfos = WpfdAddonHelper::getOneDriveBusinessFileInfos();
            if (!empty($fileInfos)) {
                if (isset($fileInfos[$active_category_id]) && isset($fileInfos[$active_category_id][$id_file])) {
                    $version = $fileInfos[$active_category_id][$id_file]['version'];
                    $description = $fileInfos[$active_category_id][$id_file]['description'];
                }
            }

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $version, $description, $targetCategoryName, $id_category);
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'googleDrive') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );

                /**
                 * Filter to delete file
                 *
                 * @param integer Category id
                 * @param string  File id
                 *
                 * @internal
                 *
                 * @ignore
                 */
                if (apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName)) {
                    /**
                     * Action upload google drive file
                     *
                     * @param array   File
                     * @param string  File name
                     * @param integer Category id
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                    list($version, $description) = $this->getOnedriveBusinessVersionDescription($active_category_id, $file);
                    /**
                     * Action update version and description after copy
                     *
                     * @param string Version
                     * @param string Description
                     * @param string Category from
                     * @param string Category TermId
                     *
                     * @internal
                     * @ignore
                     */
                    do_action('wpfd_addon_update_version_description', $version, $description, 'googleDrive', $id_category);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === 'googleDrive' && $targetCategoryName === 'onedrive_business') {
            /**
             * Filters to get google drive file info
             *
             * @param string File id
             * @param string Category type
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($googleDrive, $file, $tmpFilePath) = apply_filters(
                'wpfdAddonDownloadGoogleDriveInfo',
                $id_file,
                $active_category_id
            );
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }


                $newname = uniqid() . '.' . $file->ext;

                rename($tmpFilePath, $file_dir . $newname);
                $file_current = $file_dir . $newname;
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );
                /**
                 * Action upload onedrive business file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $file->version, $file->description, $targetCategoryName, $id_category);
                $googleDrive->delete($id_file, WpfdAddonHelper::getGoogleDriveIdByTermId($active_category_id));
            }
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'onedrive') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );

                /**
                 * Filter to delete file
                 *
                 * @param integer Category id
                 * @param string  File id
                 *
                 * @internal
                 *
                 * @ignore
                 */
                if (apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName)) {
                    /**
                     * Action upload google drive file
                     *
                     * @param array   File
                     * @param string  File name
                     * @param integer Category id
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                    list($version, $description) = $this->getOnedriveBusinessVersionDescription($active_category_id, $file);
                    /**
                     * Action update version and description after copy
                     *
                     * @param string Version
                     * @param string Description
                     * @param string Category from
                     * @param string Category TermId
                     *
                     * @internal
                     * @ignore
                     */
                    do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive', $id_category);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === 'onedrive' && $targetCategoryName === 'onedrive_business') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );

                /**
                 * Filter to delete file
                 *
                 * @param integer Category id
                 * @param string  File id
                 *
                 * @internal
                 *
                 * @ignore
                 */
                if (apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName)) {
                    /**
                     * Action upload google drive file
                     *
                     * @param array   File
                     * @param string  File name
                     * @param integer Category id
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                    list($version, $description) = $this->getOnedriveVersionDescription($active_category_id, $file);
                    /**
                     * Action update version and description after copy
                     *
                     * @param string Version
                     * @param string Description
                     * @param string Category from
                     * @param string Category TermId
                     *
                     * @internal
                     * @ignore
                     */
                    do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive_business', $id_category);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === 'dropbox') {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return boolean
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                $item         = array(
                    'title' => $file->title,
                    'ext'   => $file->ext,
                    'size'  => $file->size
                );

                /**
                 * Filter to delete file
                 *
                 * @param integer Category id
                 * @param string  File id
                 *
                 * @internal
                 *
                 * @ignore
                 */
                if (apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName)) {
                    /**
                     * Action upload google drive file
                     *
                     * @param array   File
                     * @param string  File name
                     * @param integer Category id
                     *
                     * @internal
                     */
                    do_action('wpfd_addon_upload_file', $item, $file_current, $id_category, $targetCategoryName);

                    list($version, $description) = $this->getOnedriveBusinessVersionDescription($active_category_id, $file);
                    /**
                     * Action update version and description after copy
                     *
                     * @param string Version
                     * @param string Description
                     * @param string Category from
                     * @param string Category TermId
                     *
                     * @internal
                     * @ignore
                     */
                    do_action('wpfd_addon_update_version_description', $version, $description, 'dropbox', $id_category);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === 'dropbox' && $targetCategoryName === 'onedrive_business') {
            /**
             * Filters to get dropbox file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            list($tem, $file) = apply_filters('wpfdAddonDownloadDropboxInfo', $id_file);
            $catpath_dest = WpfdBase::getFilesPath($id_category);
            if ($file) {
                if (!file_exists($catpath_dest)) {
                    mkdir($catpath_dest, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($catpath_dest . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($catpath_dest . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }

                $item = array(
                    'title' => pathinfo($file['name'], PATHINFO_FILENAME),
                    'ext'   => pathinfo($file['name'], PATHINFO_EXTENSION),
                    'size'  => $file->size
                );
                /**
                 * Action upload addon file
                 *
                 * @param array   File
                 * @param string  File name
                 * @param integer Category id
                 *
                 * @internal
                 */
                do_action('wpfd_addon_upload_file', $item, $tem, $id_category, $targetCategoryName);
                list($version, $description) = $this->getDropboxVersionDescription($active_category_id, $file);
                /**
                 * Action update version and description after copy
                 *
                 * @param string Version
                 * @param string Description
                 * @param string Category from
                 * @param string Category TermId
                 *
                 * @internal
                 * @ignore
                 */
                do_action('wpfd_addon_update_version_description', $version, $description, 'onedrive_business', $id_category);
                unlink($tem);

                $dropbox = new WpfdAddonDropbox();
                $dropbox->deleteFileDropbox($id_file);
            }
        } elseif ($activeCategoryName === 'onedrive_business' && $targetCategoryName === false) {
            /**
             * Filters to get onedrive file info
             *
             * @param string File id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            $file = apply_filters('wpfdAddonDownloadOneDriveBusinessInfo', $id_file);
            if ($file) {
                $file_dir = WpfdBase::getFilesPath($id_category);
                if (!file_exists($file_dir)) {
                    mkdir($file_dir, 0777, true);
                    $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                    $temp = fopen($file_dir . 'index.html', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                    $data = 'deny from all';
                    $temp = fopen($file_dir . '.htaccess', 'w');
                    fwrite($temp, $data);
                    fclose($temp);
                }
                $newname = uniqid() . '.' . $file->ext;
                $file_current = $file_dir . $newname;
                file_put_contents($file_current, $file->datas);
                Application::getInstance('Wpfd');
                $model       = $this->getModel();
                $id_file_new = $model->addFile(array(
                    'title'       => $file->title,
                    'id_category' => (int) $id_category,
                    'file'        => $newname,
                    'ext'         => $file->ext,
                    'size'        => $file->size
                ));
                if ($id_file_new) {
                    list($version, $description) = $this->getOnedriveBusinessVersionDescription($active_category_id, $file);
                    $this->updateLocalFileMetaData($id_file_new, $version, $description);
                    /**
                     * Filter to delete addon file
                     *
                     * @param integer Category id
                     * @param string  File id
                     *
                     * @internal
                     *
                     * @ignore
                     */
                    apply_filters('wpfd_addon_delete_file', $active_category_id, $id_file, $activeCategoryName);
                    unlink($file_current);
                }
            }
        } elseif ($activeCategoryName === false && $targetCategoryName === 'onedrive_business') {
            $modelFile       = $this->getModel('file');
            $file            = $modelFile->getFile($id_file);
            $catpath_current = WpfdBase::getFilesPath($active_category_id);
            $file_current    = $catpath_current . $file['file'];
            /**
             * Action upload onedrive file
             *
             * @param array   File
             * @param string  File name
             * @param integer Category id
             *
             * @internal
             */
            do_action('wpfd_addon_upload_file', $file, $file_current, $id_category, $targetCategoryName);

            /**
             * Action update version and description after copy
             *
             * @param string Version
             * @param string Description
             * @param string Category from
             * @param string Category TermId
             *
             * @internal
             * @ignore
             */
            do_action('wpfd_addon_update_version_description', $file['version'], $file['description'], $targetCategoryName, $id_category);
            if ($modelFile->delete($id_file)) {
                unlink($file_current);
            }
        } else {
            $this->exitStatus(esc_html__('Error: Something wrong here!', 'wpfd'));
        }
        $this->exitStatus(true, array('id_file' => $id_file));
    }

    /**
     * Reorder category
     *
     * @return void
     */
    public function reorder()
    {
        $model = $this->getModel();
        $files = Utilities::getInput('order', 'GET', 'string');
        $files = json_decode(stripslashes_deep($files));

        if ($model->reorder($files)) {
            $return = true;
        } else {
            $return = false;
        }
        $return = json_encode($return);
        $this->exitStatus($return);
    }

    /**
     * Upload version for file
     *
     * @return void
     */
    public function version()
    {
        $configModel = $this->getModel('config');
        $config = $configModel->getConfig();

        $idCategory  = Utilities::getInt('id_category') ?
            Utilities::getInt('id_category') :
            Utilities::getInt('id_category', 'POST');
        $id_file     = Utilities::getInput('id_file', 'GET', 'none') ?
            Utilities::getInput('id_file', 'GET', 'none') :
            Utilities::getInput('id_file', 'POST', 'none');

        $allowed     = $configModel->getAllowedExt();

        if (!empty($allowed)) {
            $this->allowed_ext = $allowed;
        }

        $file_dir = WpfdBase::getFilesPath($idCategory);
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $resumableIdentifier  = Utilities::getInput('resumableIdentifier', '', 'none');
            $resumableFilename    = Utilities::getInput('resumableFilename', '', 'none');
            $resumableChunkNumber = Utilities::getInt('resumableChunkNumber', '');
            $temp_dir             = $file_dir . md5($resumableIdentifier);
            $chunk_file           = $temp_dir . '/' . md5($resumableFilename) . '.part' . $resumableChunkNumber;

            if (file_exists($chunk_file)) {
                header('HTTP/1.0 200');
            } else {
                // File's chunk not yet uploaded. Upload it!
                header('HTTP/1.0 204');
            }
        }

        if (!empty($_FILES)) {
            foreach ($_FILES as $file_upload) {
                // check the error status
                if ((int) $file_upload['error'] !== 0) {
                    header('HTTP/1.0 400 Bad Request');
                    continue;
                }
                $resumableIdentifier  = html_entity_decode(
                    Utilities::getInput('resumableIdentifier', 'POST', 'none')
                );
                $resumableFilename    = html_entity_decode(Utilities::getInput('resumableFilename', 'POST', 'none'));
                $resumableChunkNumber = Utilities::getInt('resumableChunkNumber', 'POST');
                $resumableTotalSize   = Utilities::getInt('resumableTotalSize', 'POST');
                $resumableTotalChunks = Utilities::getInt('resumableTotalChunks', 'POST');
                $temp_dir             = $file_dir . md5($resumableIdentifier);
                $dest_file            = $temp_dir . '/' . md5($resumableFilename) . '.part' . $resumableChunkNumber;
                // create the temporary directory
                if (!is_dir($temp_dir)) {
                    mkdir($temp_dir, 0777, true);
                }

                $ext = strtolower(pathinfo($resumableFilename, PATHINFO_EXTENSION));
                if (!in_array(strtolower($ext), $this->allowed_ext)) {
                    $this->exitStatus(
                        esc_html__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wpfd'),
                        array('allowed ' => $this->allowed_ext)
                    );
                }
                $newname      = uniqid() . '.' . strtolower($ext);
                $file_current = $file_dir . $newname;
                $file_title   = pathinfo($resumableFilename, PATHINFO_FILENAME);
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
                $placeUpload  = apply_filters('wpfdAddonCategoryFrom', $idCategory);
                $model        = $this->getModel('file');


                if (!move_uploaded_file($file_upload['tmp_name'], $dest_file)) {
                    $this->exitStatus(esc_html__('Cannot move uploaded file', 'wpfd') . ' ' . $file_upload['name']);
                } else {
                    // check if all the parts present, and create the final destination file
                    $joinFiles = $this->createFileFromChunks(
                        $temp_dir,
                        $file_dir,
                        md5($resumableFilename),
                        $newname,
                        $resumableTotalSize,
                        $resumableTotalChunks
                    );
                    if ($joinFiles === false) {
                        $this->exitStatus('Error saving file ' . $file_upload['name']);
                    } elseif ($joinFiles === true) {
                        if ($placeUpload === 'googleDrive') {
                            /**
                             * Filters to get addon file info
                             *
                             * @param string File id
                             *
                             * @internal
                             *
                             * @ignore
                             *
                             * @return array
                             */
                            $fileInfo = apply_filters('wpfd_addon_get_file_info', $id_file, $idCategory, $placeUpload);
                            if (strtolower($ext) === strtolower($fileInfo['ext'])) {
                                $fileContent = file_get_contents($file_current);
                                /**
                                 * Filters to upload version
                                 *
                                 * @param array   File info
                                 * @param integer Category id
                                 *
                                 * @internal
                                 *
                                 * @ignore
                                 *
                                 * @return boolean
                                 */
                                if (apply_filters(
                                    'wpfdAddonUploadVersion',
                                    array(
                                        'id'          => $id_file,
                                        'newRevision' => true,
                                        'title'       => $fileInfo['title'],
                                        'data'        => $fileContent,
                                        'ext'         => strtolower($ext)
                                    ),
                                    $idCategory
                                )) {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(true, array('id_file' => $id_file, 'name' => $file_title));
                                } else {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(esc_html__("Can't upload to google Drive", 'wpfd'));
                                }
                            } else {
                                unlink($file_current);
                                $this->rrmdir($file_dir);
                                $this->exitStatus(
                                    esc_html__('You need to upload a file which has same file type with current file. Google Driver only allow same file type for new version.', 'wpfd')
                                );
                            }
                            unlink($file_current);
                            $this->rrmdir($file_dir);
                        } elseif ($placeUpload === 'dropbox') {
                            /**
                             * Filters to get addon file info
                             *
                             * @param string  File id
                             * @param integer Category id
                             *
                             * @internal
                             *
                             * @ignore
                             *
                             * @return array
                             */
                            $fileInfo =  apply_filters('wpfd_addon_get_file_info', $id_file, $idCategory, $placeUpload);
                            if (strtolower($ext) === strtolower($fileInfo['ext'])) {
                                if (apply_filters(
                                    'wpfdAddonUploadDropboxVersion',
                                    array(
                                        'newRevision'   => true,
                                        'old_file'      => $id_file,
                                        'new_file_name' => $file_title,
                                        'new_file_size' => filesize($file_current),
                                        'new_tmp_name'  => $file_current
                                    ),
                                    $idCategory
                                )) {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(true, array('id_file' => $id_file, 'name' => $file_title));
                                } else {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(esc_html__("Can't upload to Dropbox", 'wpfd'));
                                }
                            } else {
                                unlink($file_current);
                                $this->rrmdir($file_dir);
                                $this->exitStatus(
                                    esc_html__('You need to upload a file which has same file type with current file. Dropbox only allow same file type for new version.', 'wpfd')
                                );
                            }
                            unlink($file_current);
                            $this->rrmdir($file_dir);
                        } elseif ($placeUpload === 'onedrive') {
                            /**
                             * Filters to get addon file info
                             *
                             * @param string File id
                             * @param string Category id
                             *
                             * @internal
                             *
                             * @ignore
                             *
                             * @return array
                             */
                            $fileInfo =  apply_filters('wpfd_addon_get_file_info', $id_file, $idCategory, $placeUpload);
                            if ($ext === $fileInfo['ext']) {
                                $item = array(
                                    'title' => $file_title,
                                    'ext'   => $fileInfo['ext'],
                                    'size'  => filesize($file_current)
                                );
                                /**
                                 * Filters to upload Onedrive version
                                 *
                                 * @param array  Version info
                                 * @param string Category id
                                 *
                                 * @internal
                                 *
                                 * @return boolean
                                 */
                                if (apply_filters(
                                    'wpfdAddonUploadOneDriveVersion',
                                    array(
                                        'old_id'    => $id_file,
                                        'file_name' => $fileInfo['title'] . '.' . $fileInfo['ext'],
                                        'file_size' => filesize($file_current),
                                        'file_pic'  => $item,
                                        'tmp_name'  => $file_current
                                    ),
                                    $idCategory
                                )) {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(true, array('id_file' => $id_file, 'name' => $file_title));
                                } else {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(esc_html__("Can't upload to OneDrive", 'wpfd'));
                                }
                            } else {
                                unlink($file_current);
                                $this->rrmdir($file_dir);
                                $this->exitStatus(
                                    esc_html__('You need to upload a file which has same file type with current file. OneDrive only allow same file type for new version.', 'wpfd')
                                );
                            }
                            unlink($file_current);
                            $this->rrmdir($file_dir);
                        } elseif ($placeUpload === 'onedrive_business') {
                            /**
                             * Filters to get addon file info
                             *
                             * @param string File id
                             * @param string Category id
                             *
                             * @internal
                             *
                             * @ignore
                             *
                             * @return array
                             */
                            $fileInfo =  apply_filters('wpfd_addon_get_file_info', $id_file, $idCategory, $placeUpload);
                            if ($ext === $fileInfo['ext']) {
                                $item = array(
                                    'title' => $file_title,
                                    'ext'   => $fileInfo['ext'],
                                    'size'  => filesize($file_current)
                                );
                                /**
                                 * Filters to upload Onedrive version
                                 *
                                 * @param array  Version info
                                 * @param string Category id
                                 *
                                 * @internal
                                 *
                                 * @return boolean
                                 */
                                if (apply_filters(
                                    'wpfdAddonUploadOneDriveBusinessVersion',
                                    array(
                                        'old_id'    => $id_file,
                                        'file_name' => $fileInfo['title'] . '.' . $fileInfo['ext'],
                                        'file_size' => filesize($file_current),
                                        'file_pic'  => $item,
                                        'tmp_name'  => $file_current
                                    ),
                                    $idCategory
                                )) {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(true, array('id_file' => $id_file, 'name' => $file_title));
                                } else {
                                    unlink($file_current);
                                    $this->rrmdir($file_dir);
                                    $this->exitStatus(esc_html__("Can't upload to OneDrive Business", 'wpfd'));
                                }
                            } else {
                                unlink($file_current);
                                $this->rrmdir($file_dir);
                                $this->exitStatus(
                                    esc_html__('You need to upload a file which has same file type with current file. OneDrive Business only allow same file type for new version.', 'wpfd')
                                );
                            }
                            unlink($file_current);
                            $this->rrmdir($file_dir);
                        } else {
                            $file = $model->getFile($id_file);
                            if ($file['ext'] !== $ext) {
                                $this->exitStatus(
                                    esc_html__('You can only upload same file type for version', 'wpfd'),
                                    array('allowed ' => array($file['ext']))
                                );
                            }
                            $result = $model->updateFile($id_file, array(
                                'title' => $file['title'],
                                'file'  => $newname,
                                'ext'   => $ext,
                                'size'  => filesize(WpfdBase::getFilesPath($file['catid']) . $newname)
                            ));
                            if (!$result) {
                                unlink(WpfdBase::getFilesPath($file['catid']) . $newname);
                                $this->exitStatus(esc_html__('Can\'t save to database', 'wpfd'));
                            }

                            //add old file into version history
                            $model->addVersion($file);
                            // Reindex new version file
                            Application::getInstance('Wpfd');
                            /* @var WpfdModelGeneratepreview $generatePreviewModel */
                            $generatePreviewModel = $this->getModel('generatepreview');
                            $ftsModel = $this->getModel('fts');
                            $ftsModel->wpfdPostReindex($id_file);

                            $generatePreviewModel->removeFileFromQueue($id_file);
                            $generatePreviewModel->addFileToQueue($id_file);

                            $versionLimit = isset($config['versionlimit']) ? (int) $config['versionlimit'] : 10;

                            // Get versions to delete
                            $model->deleteOldVersions($id_file, $idCategory, $versionLimit);

                            $this->exitStatus(true, array('id_file' => $id_file, 'name' => $file_title));
                        }
                    }
                }
            }
        } else {
            $this->exitStatus(esc_html__("Can't Upload Files", 'wpfd'));
        }
    }

    /**
     * Import files
     *
     * @return void
     */
    public function import()
    {
        if (!is_admin()) {
            $this->exitStatus(esc_html__("You don't have the sufficient permissions", 'wpfd'));
        }
        $id_category = Utilities::getInt('id_category');
        if ($id_category <= 0) {
            $this->exitStatus(esc_html__('Category not found', 'wpfd'));
        }
        $file_dir = WpfdBase::getFilesPath($id_category);
        if (!file_exists($file_dir)) {
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
        }

        $files = Utilities::getInput('files', 'POST', 'none');

        if (!empty($files)) {
            $count       = 0;
            $configModel = $this->getModel('config');
            $allowed     = $configModel->getAllowedExt();
            if (!empty($allowed)) {
                $this->allowed_ext = $allowed;
            }
            foreach ($files as $file) {
                $file = get_home_path() . stripslashes($file);

                if (!in_array(wpfd_getext($file), $this->allowed_ext)) {
                    $this->exitStatus(
                        esc_html__('This type of file is not allowed to be uploaded. You can add new file types in the plugin configuration', 'wpfd'),
                        array('allowed ' => $this->allowed_ext)
                    );
                }
                $newname = uniqid() . '.' . strtolower(wpfd_getext($file));

                if (!copy($file, $file_dir . $newname)) {
                    $this->exitStatus(esc_html__('Cant move uploaded file', 'wpfd'));
                }
                chmod($file_dir . $newname, 0777);
                //Insert new image into databse
                $model   = $this->getModel();
                // Fix wrong file name
                setlocale(LC_ALL, 'C.UTF-8');

                $id_file = $model->addFile(array(
                    'title'       => preg_replace('#\.[^.]*$#', '', basename($file)),
                    'id_category' => $id_category,
                    'file'        => $newname,
                    'ext'         => strtolower(wpfd_getext($file)),
                    'size'        => filesize($file_dir . $newname)
                ));
                if (!$id_file) {
                    unlink($file_dir . $newname);
                    $this->exitStatus(esc_html__('Cannot save file to DB', 'wpfd'));
                }
                $count++;
            }
            $this->exitStatus(true, array('nb' => $count));
        }
        $this->exitStatus(esc_html__('Error while importing', 'wpfd'));
    }

    /**
     * Add a remote file url
     *
     * @return void
     */
    public function addremoteurl()
    {
        $id_category = Utilities::getInt('id_category', 'GET');
        if ($id_category <= 0) {
            $this->exitStatus(esc_html__('Wrong Category', 'wpfd'));
        }
        $remote_title = Utilities::getInput('remote_title', 'POST', 'string');
        $remote_url   = Utilities::getInput('remote_url', 'POST', 'none');
        $remote_type  = Utilities::getInput('remote_type', 'POST', 'none');

        if ($remote_title === '') {
            $this->exitStatus(esc_html__('Enter title', 'wpfd'));
        } elseif ($remote_type === '') {
            $this->exitStatus(esc_html__('Enter type', 'wpfd'));
        } elseif ($remote_url === '') {
            $this->exitStatus(esc_html__('Enter url', 'wpfd'));
        } else {
            if (!preg_match('(http://|https://)', $remote_url)) {
                $this->exitStatus(sprintf(esc_html__('%s is not a valid URL', 'wpfd'), $remote_url));
            }
        }
        //Insert new image into databse
        $model = $this->getModel();

        $id_file = $model->addFile(array(
            'title'       => $remote_title,
            'id_category' => (int) $id_category,
            'file'        => $remote_url,
            'ext'         => $remote_type,
            'size'        => wpfd_remote_file_size($remote_url)
        ), true);

        if (!$id_file) {
            $this->exitStatus(esc_html__("Can't save to database", 'wpfd'));
        }

        $this->exitStatus(true, array('id_file' => $id_file, 'name' => $remote_url));
    }

    /**
     * Show column
     *
     * @return void
     */
    public function showcolumn()
    {
        $column_show  = Utilities::getInput('column_show', 'POST', 'none');
        $lists        = ($column_show !== null) ? $column_show : array();
        $string_lists = implode(',', $lists);
        setcookie('wpfd_show_columns', $string_lists, time() + (86400 * 30), '/');
        wp_send_json(true);
    }

    /**
     * Delete a directory RECURSIVELY
     *
     * @param string $dir Directory path
     *
     * @link http://php.net/manual/en/function.rmdir.php
     *
     * @return void;
     */
    public function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        $this->rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    /**
     * Check if all the parts exist, and
     * gather all the parts of the file together
     *
     * @param string  $temp_dir        The temporary directory holding all the parts of the file
     * @param string  $destination_dir The directory to save joined file
     * @param string  $fileName        The original file name
     * @param string  $newName         The new unique file name
     * @param string  $totalSize       Original file size (in bytes)
     * @param integer $total_files     Total files
     *
     * @return   boolean true   If success
     *                 false    If got error while joining
     *                 null     If not uploaded all chunk yet
     * @internal param string $chunkSize Each chunk size (in bytes)
     */
    public function createFileFromChunks($temp_dir, $destination_dir, $fileName, $newName, $totalSize, $total_files)
    {
        // count all the parts of this file
        $total_files_on_server_size = 0;
        $temp_total                 = 0;
        foreach (scandir($temp_dir) as $file) {
            $temp_total                 = $total_files_on_server_size;
            $tempfilesize               = filesize($temp_dir . '/' . $file);
            $total_files_on_server_size = $temp_total + $tempfilesize;
        }
        // check that all the parts are present
        // If the Size of all the chunks on the server is equal to the size of the file uploaded.
        if ($total_files_on_server_size >= $totalSize) {
            // create the final destination file
            $file = fopen($destination_dir . '/' . $newName, 'w');
            if ($file !== false) {
                for ($i = 1; $i <= $total_files; $i++) {
                    fwrite($file, file_get_contents($temp_dir . '/' . $fileName . '.part' . $i));
                }
                fclose($file);
            } else {
                return false;
            }
            // rename the temporary directory (to avoid access from other
            // concurrent chunks uploads) and than delete it
            if (rename($temp_dir, $temp_dir . '_UNUSED')) {
                $this->rrmdir($temp_dir . '_UNUSED');
            } else {
                $this->rrmdir($temp_dir);
            }

            return true;
        }

        return null;
    }

    /**
     * Copy File meta data from dropbox to local
     *
     * @param integer $dropboxCatId Dropbox term id
     * @param array   $dropboxFile  Dropbox file array
     * @param integer $newFileId    New local file id
     *
     * @return void
     */
    private function copyMetaDropboxToLocal($dropboxCatId, $dropboxFile, $newFileId)
    {
        list($version, $description) = $this->getDropboxVersionDescription($dropboxCatId, $dropboxFile);

        // Update version and description
        $this->updateLocalFileMetaData($newFileId, $version, $description);
    }

    /**
     * Copy File meta data from Google Drive to local
     *
     * @param integer $id_file_new New local file id
     * @param object  $file        Google file object
     *
     * @return void
     */
    private function copyMetaGoogleDriveToLocal($id_file_new, $file)
    {
        if (!isset($file->version)) {
            $file->version = isset($file->versionNumber) ? $file->versionNumber : '';
        }

        $this->updateLocalFileMetaData($id_file_new, $file->version, $file->description, $file->file_tags);
    }

    /**
     * Copy File meta data from onedrive to local
     *
     * @param integer $onedriveCatId Onedrive category id
     * @param object  $onedriveFile  File object
     * @param integer $newFileId     File id
     *
     * @return void
     */
    private function copyMetaOnedriveToLocal($onedriveCatId, $onedriveFile, $newFileId)
    {
        list($version, $description) = $this->getOnedriveVersionDescription($onedriveCatId, $onedriveFile);

        $this->updateLocalFileMetaData($newFileId, $version, $description);
    }

    /**
     * Update file version and description
     *
     * @param integer $fileId      File id
     * @param string  $version     File version
     * @param string  $description File Description
     * @param string  $fileTags    File tags
     *
     * @return void
     */
    private function updateLocalFileMetaData($fileId, $version = '', $description = '', $fileTags = '')
    {
        $newFileMeta = get_post_meta($fileId, '_wpfd_file_metadata', true);
        // Reset some data
        $newFileMeta['file_multi_category']     = array();
        $newFileMeta['file_multi_category_old'] = '';
        $newFileMeta['hits']                    = 0;
        // Copy to new file metadata
        $newFileMeta['version'] = $version;
        $newFileMeta['versionNumber'] = $version;
        if ($fileTags !== '') {
            $newFileMeta['file_tags'] = $fileTags;
        }
        update_post_meta($fileId, '_wpfd_file_metadata', $newFileMeta);

        // Update new file description
        wp_update_post(array(
            'ID'           => $fileId,
            'post_excerpt' => $description
        ));
    }

    /**
     * Get Dropbox Version and Description
     *
     * @param integer $dropboxTermId Dropbox Term id
     * @param array   $dropboxFile   Dropbox file object
     *
     * @return array
     */
    private function getDropboxVersionDescription($dropboxTermId, $dropboxFile)
    {
        $dropboxFileMetas = get_option('_wpfdAddon_dropbox_fileInfo');
        $version          = '';
        $description      = '';

        if (!empty($dropboxFileMetas) && isset($dropboxFileMetas[$dropboxTermId]) && isset($dropboxFileMetas[$dropboxTermId][$dropboxFile['id']])) {
            $description = isset($dropboxFileMetas[$dropboxTermId][$dropboxFile['id']]['description']) ?
                $dropboxFileMetas[$dropboxTermId][$dropboxFile['id']]['description'] : '';
            $version     = isset($dropboxFileMetas[$dropboxTermId][$dropboxFile['id']]['version']) ?
                $dropboxFileMetas[$dropboxTermId][$dropboxFile['id']]['version'] : '';
        }

        return array($version, $description);
    }

    /**
     * Get Onedrive Version and Description
     *
     * @param integer $onedriveTermId Onedrive Term id
     * @param object  $onedriveFile   Onedrive file object
     *
     * @return array
     */
    private function getOnedriveVersionDescription($onedriveTermId, $onedriveFile)
    {
        $onedriveMeta     = get_option('_wpfdAddon_onedrive_fileInfo');
        $version          = '';
        $description      = '';
        $onedriveFile->id = str_replace('!', '-', $onedriveFile->id);

        if (!empty($onedriveMeta) && isset($onedriveMeta[$onedriveTermId]) && isset($onedriveMeta[$onedriveTermId][$onedriveFile->id])) {
            $description = isset($onedriveMeta[$onedriveTermId][$onedriveFile->id]['description']) ?
                $onedriveMeta[$onedriveTermId][$onedriveFile->id]['description'] : '';
            $version     = isset($onedriveMeta[$onedriveTermId][$onedriveFile->id]['version']) ?
                $onedriveMeta[$onedriveTermId][$onedriveFile->id]['version'] : '';
        }

        return array($version, $description);
    }
    /**
     * Get Onedrive Business Version and Description
     *
     * @param integer $onedriveTermId Onedrive Term id
     * @param object  $onedriveFile   Onedrive file object
     *
     * @return array
     */
    private function getOnedriveBusinessVersionDescription($onedriveTermId, $onedriveFile)
    {
        $onedriveMeta     = get_option('_wpfdAddon_onedrive_business_fileInfo');
        $version          = '';
        $description      = '';
        $onedriveFile->id = str_replace('!', '-', $onedriveFile->id);

        if (!empty($onedriveMeta) && isset($onedriveMeta[$onedriveTermId]) && isset($onedriveMeta[$onedriveTermId][$onedriveFile->id])) {
            $description = isset($onedriveMeta[$onedriveTermId][$onedriveFile->id]['description']) ?
                $onedriveMeta[$onedriveTermId][$onedriveFile->id]['description'] : '';
            $version     = isset($onedriveMeta[$onedriveTermId][$onedriveFile->id]['version']) ?
                $onedriveMeta[$onedriveTermId][$onedriveFile->id]['version'] : '';
        }

        return array($version, $description);
    }
    /**
     * Move dropbox meta data
     *
     * @param integer $fromCatId Source term id
     * @param string  $id_file   Dropbox file id
     * @param integer $toCatId   New term id
     *
     * @return void
     */
    private function moveDropboxMeta($fromCatId, $id_file, $toCatId)
    {
        // Move file info in database to new category
        $dropboxFileMetas = WpfdAddonHelper::getDropboxFileInfos();
        if (!empty($dropboxFileMetas) && isset($dropboxFileMetas[$fromCatId]) && isset($dropboxFileMetas[$fromCatId][$id_file])) {
            $oldMeta = isset($dropboxFileMetas[$fromCatId][$id_file]) ? $dropboxFileMetas[$fromCatId][$id_file] : '';
            // Remove old param
            unset($dropboxFileMetas[$fromCatId][$id_file]);

            // Update new param on target category
            $dropboxFileMetas[$toCatId][$id_file] = $oldMeta;
            WpfdAddonHelper::setDropboxFileInfos($dropboxFileMetas);
        }
    }
}
