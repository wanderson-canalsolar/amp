<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\View;
use Joomunited\WPFramework\v1_0_5\Utilities;
use Joomunited\WPFramework\v1_0_5\Form;

defined('ABSPATH') || die();

/**
 * Class WpfdViewFile
 */
class WpfdViewFile extends View
{
    /**
     * Render view file
     *
     * @param null $tpl Template name
     *
     * @return void
     */
    public function render($tpl = null)
    {
        Application::getInstance('Wpfd');
        $model      = $this->getModel('file');
        $idCategory = null;
        $fileId     = null;
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'wpfd-security')) {
            wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
        }
        if (isset($_POST['fileInfo'][0])) {
            if (isset($_POST['fileInfo'][0]['fileId'])) {
                $fileId = esc_html($_POST['fileInfo'][0]['fileId']);
            }
            if (isset($_POST['fileInfo'][0]['catid'])) {
                $idCategory = (int) $_POST['fileInfo'][0]['catid'];
            }
        }

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
            /**
             * Filter to get addon file info
             *
             * @param string  File id
             * @param integer Category term id
             * @param string  Category from
             *
             * @return array
             *
             * @internal
             *
             * @ignore
             */
            $datas = (array) apply_filters('wpfd_addon_get_file_info', $fileId, $idCategory, $categoryFrom);
        } else {
            $datas = $model->getFile($fileId);
        }
        $layout = Utilities::getInput('layout', 'GET', 'string');
        if ($layout === 'versions') {
            $this->file_id = $datas['ID'];
            if ($categoryFrom === 'dropbox') {
                $this->versions = apply_filters('wpfdAddonDropboxVersionInfo', $datas['ID'], $idCategory);
            } elseif ($categoryFrom === 'googleDrive') {
                $this->versions = apply_filters('wpfdAddonGetListVersions', $datas['ID'], $idCategory);
            } else {
                $this->versions = $model->getVersions($datas['ID'], $idCategory);
            }

            parent::render($layout);
            wp_die();
        }
        // Fix wrong instance
        Application::getInstance('Wpfd');
        $form = new Form();

        /**
         * Filter to update data before load to fields
         *
         * @param array Data load to fields
         *
         * @return array
         */
        $datas = apply_filters('wpfd_file_params', $datas);
        if ($form->load('file', $datas)) {
            $this->form = $form->render('link');
        }

        $tags = get_terms('wpfd-tag', array(
            'orderby'    => 'count',
            'hide_empty' => 0,
        ));
        if ($tags) {
            $allTagsFiles = array();
            foreach ($tags as $tag) {
                $allTagsFiles[] = '' . esc_html($tag->slug);
            }
            $this->allTagsFiles = '["' . implode('","', $allTagsFiles) . '"]';
        } else {
            $this->allTagsFiles = '[]';
        }
        parent::render($tpl);
        wp_die();
    }
}
