<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Model;

defined('ABSPATH') || die();

/**
 * Class WpfdModelFilefront
 */
class WpfdModelFilefront extends Model
{

    /**
     * Get category by file id
     *
     * @param integer $id_file File id
     *
     * @return integer
     */
    public function getFileCategory($id_file)
    {
        $catid     = 0;
        $term_list = wp_get_post_terms($id_file, 'wpfd-category', array('fields' => 'ids'));
        if (!is_wp_error($term_list)) {
            $catid = $term_list[0];
        }

        return $catid;
    }

    /**
     * Check valid ref catid
     *
     * @param integer $id       File id
     * @param integer $refCatId Ref category id to check
     *
     * @return boolean
     */
    public function isValidRefCatId($id, $refCatId)
    {
        $metaData = get_post_meta($id, '_wpfd_file_metadata', true);

        if (!is_array($metaData)) {
            return false;
        }

        if (!isset($metaData['file_multi_category'])) {
            return false;
        }

        $refCatIds = $metaData['file_multi_category'];
        $refCatIds = array_map(function ($cId) {
            return intval($cId);
        }, $refCatIds);
        if (in_array(intval($refCatId), $refCatIds)) {
            return true;
        }

        return false;
    }

    /**
     * Get file info by ID
     *
     * @param integer $id_file File id
     * @param integer $rootcat Parent category
     *
     * @return mixed
     */
    public function getFile($id_file, $rootcat = 0)
    {
        $app            = Application::getInstance('Wpfd', __FILE__);
        $modelConfig    = $this->getInstance('configfront');
        $modelCategory  = $this->getInstance('categoryfront');
        $modelTokens    = $this->getInstance('tokens');
        $token          = '';
        $params         = $modelConfig->getGlobalConfig();
        $user           = wp_get_current_user();
        $WpfdBasePath   = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR;
        $WpfdBasePath  .= 'classes' . DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $WpfdBasePath;
        $rmdownloadext  = (int) WpfdBase::loadValue($params, 'rmdownloadext', 1) === 1;
        $roles          = array();
        if (!class_exists('WpfdHelperFile')) {
            require_once WPFD_PLUGIN_DIR_PATH . 'app/site/helpers/WpfdHelperFile.php';
        }
        foreach ($user->roles as $role) {
            $roles[] = strtolower($role);
        }
        // Fix conflict plugin Go7 Pricing Table
        remove_all_filters('posts_fields');
        $row            = get_post($id_file, OBJECT);
        $expiredFile    = false;
        $fileMetadata   = get_post_meta($id_file, '_wpfd_file_metadata', true);
        $productLinked  = get_post_meta($id_file, '_wpfd_products_linked', true);
        if ((WpfdHelperFile::wpfdIsExpired((int)$id_file) === true && $productLinked === '') ||
            (WpfdHelperFile::wpfdIsExpired((int)$id_file) === true && is_array($fileMetadata)
                && isset($fileMetadata['woo_permission']) && $fileMetadata['woo_permission'] === 'both_woo_and_wpfd_permission')) {
            $expiredFile = true;
        }
        if (is_wp_error($row) || $row === false || !isset($row->post_status) || $row->post_status === 'private' || $row->post_date_gmt > current_time('mysql', 1)
            || $expiredFile === true) {
            return false;
        }
        /**
         * Filter to change file title
         *
         * @param string  File title
         * @param integer File id
         *
         * @return string
         */
        $row->title       = apply_filters('wpfd_file_title', $row->post_title, $row->ID);
        $row->description = trim($row->post_excerpt);
        $row->created_time = get_date_from_gmt($row->post_date_gmt);
        $row->modified_time = get_date_from_gmt($row->post_modified_gmt);
        $row->created     = mysql2date(
            WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
            $row->created_time
        );
        $row->modified    = mysql2date(
            WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
            $row->modified_time
        );
        $metadata         = get_post_meta($id_file, '_wpfd_file_metadata', true);
        if (!is_wp_error($metadata) && !empty($metadata)) {
            foreach ($metadata as $key => $value) {
                $row->$key = $value;
            }
        }
        $term_list     = wp_get_post_terms($id_file, 'wpfd-category', array('fields' => 'ids'));
        if (empty($term_list)) {
            return false;
        }
        $wpfd_term     = get_term($term_list[0], 'wpfd-category');
        $row->catname  = sanitize_title($wpfd_term->name);
        $row->cattitle = $wpfd_term->name;
        if (!is_wp_error($term_list)) {
            $row->catid = $term_list[0];
        } else {
            $row->catid = 0;
        }
        if (isset($row->version)) {
            $row->versionNumber = $row->version;
        }
        $remote_url  = isset($metadata['remote_url']) ? $metadata['remote_url'] : false;
        $viewer_type = WpfdBase::loadValue($params, 'use_google_viewer', 'lightbox');

        $extension_viewer_list = 'png,jpg,pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4';
        $extension_viewer      = explode(',', WpfdBase::loadValue($params, 'extension_viewer', $extension_viewer_list));
        $extension_viewer      = array_map('trim', $extension_viewer);

        if ($viewer_type !== 'no' &&
            in_array($row->ext, $extension_viewer)
            && ($remote_url === false)
        ) {
            $row->viewer_type = $viewer_type;

            //check access
            $category     = $modelCategory->getCategory($row->catid);
            $rootcategory = $modelCategory->getCategory($rootcat);
            if (empty($category) || is_wp_error($category)) {
                return false;
            }
            $roles = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $allows = array_intersect($roles, $category->roles);

            if (WpfdHelperFile::isMediaFile($row->ext)) {
                if ((int) $category->access === 1) {
                    if (!empty($allows)) {
                        $row->viewerlink = WpfdHelperFile::getMediaViewerUrl($row->ID, $row->catid, $row->ext);
                    }
                } else {
                    $row->viewerlink = WpfdHelperFile::getMediaViewerUrl($row->ID, $row->catid, $row->ext);
                }
            } else {
                if (is_user_logged_in()) {
                    $sessionToken = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null;
                    if ($sessionToken === null) {
                        $token                 = $modelTokens->createToken();
                        $_SESSION['wpfdToken'] = $token;
                    } else {
                        $tokenId = $modelTokens->tokenExists($sessionToken);
                        if ($tokenId) {
                            $modelTokens->updateToken($tokenId);
                            $token = $sessionToken;
                        } else {
                            $token                 = $modelTokens->createToken();
                            $_SESSION['wpfdToken'] = $token;
                        }
                    }
                }

                if ((int) $category->access === 1) {
                    if (!empty($allows)) {
                        $row->viewerlink = WpfdHelperFile::getViewerUrl($row->ID, $row->catid, $token);
                    }
                } else {
                    $row->viewerlink = WpfdHelperFile::getViewerUrl($row->ID, $row->catid, $token);
                }
            }
            // Crop file titles
            $row->crop_title = $row->post_title;
            if ($rootcat) {
                $row->crop_title = WpfdBase::cropTitle(
                    $rootcategory->params,
                    $rootcategory->params['theme'],
                    $row->post_title
                );
            } else {
                $row->crop_title = WpfdBase::cropTitle($category->params, $category->params['theme'], $row->post_title);
            }
        }
        /**
         * Filter to change file title
         *
         * @param string  File title
         * @param integer File id
         *
         * @return string
         *
         * @ignore
         */
        $row->crop_title = apply_filters('wpfd_file_title', $row->crop_title, $row->ID);
        $open_pdf_in = WpfdBase::loadValue($params, 'open_pdf_in', 0);

        if ((int) $open_pdf_in === 1 && strtolower($row->ext) === 'pdf') {
            if (is_user_logged_in()) {
                $token = isset($_SESSION['wpfdToken']) ? $_SESSION['wpfdToken'] : null;
            }
            $row->openpdflink = WpfdHelperFile::getPdfUrl($row->ID, $row->catid, $token) . '&preview=1';
        }

        $config = get_option('_wpfd_global_config');
        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download';
        } else {
            $seo_uri = rawurlencode($config['uri']);
        }
        $row->seouri = $seo_uri;

        $perlink       = get_option('permalink_structure');
        $rewrite_rules = get_option('rewrite_rules');

        if (wpfd_can_download_files()) {
            if (!empty($rewrite_rules)) {
                if (strpos($perlink, 'index.php')) {
                    $linkdownload      = get_site_url() . '/index.php/' . $seo_uri . '/' . $row->catid . '/';
                    $linkdownload      .= $row->catname . '/' . $row->ID . '/' . $row->post_name;
                    $row->linkdownload = $linkdownload;
                } else {
                    $linkdownload      = get_site_url() . '/' . $seo_uri . '/' . $row->catid . '/' . $row->catname;
                    $linkdownload      .= '/' . $row->ID . '/' . $row->post_name;
                    $row->linkdownload = $linkdownload;
                }
                if (isset($row->ext) && $row->ext && !$rmdownloadext) {
                    $row->linkdownload .= '.' . $row->ext;
                }
            } else {
                $linkdownload      = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=file.download';
                $linkdownload      .= '&wpfd_category_id=' . $row->catid . '&wpfd_file_id=' . $row->ID;
                $row->linkdownload = $linkdownload;
            }
        } else {
            $row->linkdownload = '';
        }

        $row->file_custom_icon = isset($metadata['file_custom_icon']) && !empty($metadata['file_custom_icon']) ?
            $metadata['file_custom_icon'] : '';
        /**
         * Filter file info in front
         *
         * @param object File object
         *
         * @return object
         */
        return apply_filters('wpfd_file_info', $row);
    }

    /**
     * Get full info for file
     *
     * @param integer $id_file File id
     *
     * @return boolean|stdClass
     */
    public function getFullFile($id_file)
    {
        $app         = Application::getInstance('Wpfd');
        $modelConfig = $this->getInstance('configfront');
        $params      = $modelConfig->getGlobalConfig();

        // Fix conflict plugin Go7 Pricing Table
        remove_all_filters('posts_fields');
        $row = get_post((int) $id_file, OBJECT);
        if ($row === false) {
            return false;
        }
        $path_wpfdbase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_wpfdbase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_wpfdbase;

        $ob              = new stdClass();
        $ob->ID          = $row->ID;
        $ob->author      = $row->post_author;
        $ob->post_name   = $row->post_name;
        /**
         * Filter to change file title
         *
         * @param string  File title
         * @param integer File id
         *
         * @return string
         *
         * @ignore
         */
        $ob->title       = apply_filters('wpfd_file_title', $row->post_title, $row->ID);
        $ob->description = $row->post_excerpt;
        $ob->created     = mysql2date(
            WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
            get_date_from_gmt($row->post_date_gmt)
        );
        $ob->modified    = mysql2date(
            WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
            get_date_from_gmt($row->post_modified_gmt)
        );
        $metadata        = get_post_meta($id_file, '_wpfd_file_metadata', true);
        // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
        if (is_countable($metadata) && count($metadata)) {
            foreach ($metadata as $key => $value) {
                $ob->$key = $value;
            }
        }
        $term_list = wp_get_post_terms($id_file, 'wpfd-category', array('fields' => 'ids'));
        if (!is_wp_error($term_list)) {
            $ob->catid = $term_list[0];
        } else {
            $ob->catid = 0;
        }

        /**
         * Filter file info in front
         *
         * @param object File object
         *
         * @return object
         *
         * @ignore
         */
        return apply_filters('wpfd_file_info', $ob);
    }

    /**
     * Increase hit for file
     *
     * @param integer $id_file File id
     *
     * @return boolean
     */
    public function hit($id_file)
    {
        $metadata         = get_post_meta((int) $id_file, '_wpfd_file_metadata', true);
        $hits             = (int) $metadata['hits'];
        $metadata['hits'] = $hits + 1;
        update_post_meta((int) $id_file, '_wpfd_file_metadata', $metadata);

        return true;
    }

    /**
     * Add a file for statistic when downloading file
     *
     * @param integer|string $file_id File id
     * @param string         $date    Date
     *
     * @return void
     */
    public function addChart($file_id, $date)
    {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            'INSERT INTO ' . $wpdb->prefix . 'wpfd_statistics (related_id,type,date,count) VALUES (%s, "default", %s, 1)',
            $file_id,
            $date
        ));
    }

    /**
     * Add file to chart
     *
     * @param integer|string $file_id File id
     *
     * @return boolean
     */
    public function addCountChart($file_id)
    {
        global $wpdb;
        $date       = date('Y-m-d');
        $object = $wpdb->get_row($wpdb->prepare(
            'SELECT * FROM ' . $wpdb->prefix . 'wpfd_statistics WHERE related_id=%s AND date=%s',
            $file_id,
            $date
        ));
        if ($object) {
            $wpdb->query($wpdb->prepare(
                'UPDATE ' . $wpdb->prefix . 'wpfd_statistics SET count=(count+1) WHERE related_id=%s AND date=%s',
                $file_id,
                $date
            ));
        } else {
            $this->addChart($file_id, $date);
        }

        return true;
    }
}
