<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdViewFile
 */
class WpfdViewFile extends View
{
    /**
     * Display a file
     *
     * @param string $tpl Template name
     *
     * @return mixed|string|void
     */
    public function render($tpl = null)
    {
        $categoryid  = Utilities::getInput('categoryid', 'GET', 'none');
        $idFile      = Utilities::getInput('id', 'GET', 'none');

        $model       = $this->getModel('filefront');
        $modelTokens = $this->getModel('tokens');
        $modelCat    = $this->getModel('categoryfront');

        $file        = null;
        $token       = $modelTokens->getOrCreateNew();
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
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $categoryid);
        if ($categoryFrom === 'googleDrive') {
            $file = apply_filters('wpfdAddonGetGoogleDriveFile', $idFile, $categoryid, $token);
        } elseif ($categoryFrom === 'dropbox') {
            $file = apply_filters('wpfdAddonGetDropboxFile', $idFile, $categoryid, $token);
        } elseif ($categoryFrom === 'onedrive') {
            $file = apply_filters('wpfdAddonGetOneDriveFile', $idFile, $categoryid, $token);
        } elseif ($categoryFrom === 'onedrive_business') {
            $file = apply_filters('wpfdAddonGetOneDriveBusinessFile', $idFile, $categoryid, $token);
        }
        if (!$file || ($file === $idFile)) {
            $file  = $model->getFile(Utilities::getInt('id'), Utilities::getInt('rootcat'));
        }

        // Crop file titles
        $rootcat      = Utilities::getInt('rootcat');
        $categorys    = $modelCat->getCategory($categoryid);
        $rootcategory = $modelCat->getCategory($rootcat);
        $file         = (object) ($file);
        if ($rootcat) {
            $file->crop_title = WpfdBase::cropTitle(
                $rootcategory->params,
                $rootcategory->params['theme'],
                $file->post_title
            );
        } else {
            $file->crop_title = WpfdBase::cropTitle($categorys->params, $categorys->params['theme'], $file->post_title);
        }

        if (isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
            if (strpos($file->file_custom_icon, site_url()) !== 0) {
                $file->file_custom_icon = site_url() . $file->file_custom_icon;
            }
        }

        if (!$file || ($file === $idFile)) {
            return wp_json_encode(new stdClass());
        }
        //fix : access check
        $content       = new stdClass();
        $content->file = $file;
        echo wp_json_encode($content);
        exit();
    }
}
