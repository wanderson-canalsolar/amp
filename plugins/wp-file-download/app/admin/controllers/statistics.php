<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

use Joomunited\WPFramework\v1_0_5\Controller;
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdControllerStatistics
 */
class WpfdControllerStatistics extends Controller
{
    /**
     * Export statistics as csv
     *
     * @return void
     */
    public function export()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['wpfd_statistics_nonce']) ||
                !wp_verify_nonce($_POST['wpfd_statistics_nonce'], 'wpfd_statistics')) {
                wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
            }
        }
        $model = $this->getModel('statistics');
        if (!$model instanceof WpfdModelStatistics) {
            die(esc_html__('Wrong model', 'wpfd'));
        }
        // User must login to generate/download this statistics
        $currentUser = wp_get_current_user();
        $wpUploadDir = wp_upload_dir();
        $csvExportDir = 'csv';

        if (!isset($currentUser->user_login)) {
            wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
        }
        /*
         * Filter to get wpfd upload folder
         * @param string Wpfd upload folder
         */
        $wpfdUploadDir = apply_filters('wpfd_upload_base_dir', 'wpfd');
        $wpfdDirname = $wpUploadDir['basedir'] . DIRECTORY_SEPARATOR . $wpfdUploadDir . DIRECTORY_SEPARATOR . $csvExportDir;
        if (!file_exists($wpfdDirname)) {
            mkdir($wpfdDirname, 0777, true);
            $data = '<html><body bgcolor="#FFFFFF"></body></html>';
            $file = fopen($wpfdDirname . DIRECTORY_SEPARATOR . 'index.html', 'w');
            fwrite($file, $data);
            fclose($file);
            $data = 'deny from all';
            $file = fopen($wpfdDirname . DIRECTORY_SEPARATOR . '.htaccess', 'w');
            fwrite($file, $data);
            fclose($file);
        }

        $files = $model->getItems();
        if (!empty($files)) {
            foreach ($files as $file) {
                $cat = wp_get_post_terms($file->ID, 'wpfd-category');
                if (!empty($cat)) {
                    $file->cattitle = $cat[0]->name;
                }
            }
        }
        $fileName = md5(date('Y_m_d_H_i_s') .'_wpfd_statistics');
        $filePath = $wpfdDirname . DIRECTORY_SEPARATOR . $fileName . '.csv';
        $fp = fopen($filePath, 'wb');
        foreach ($files as $file) {
            $data = array($file->ID, $file->post_title, $file->cattitle);
            if (isset($file->uid)) {
                $data[] = $file->uid;
                $data[] = isset($file->display_name) ? $file->display_name : esc_html__('Guess', 'wpfd');
            }
            $data[] = $file->count_hits;
            fputcsv($fp, $data);
        }
        fclose($fp);
        wp_send_json_success(array('code' => $fileName));
        die();
    }

    /**
     * Download statistics file
     *
     * @return void
     */
    public function download()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            if (!isset($_GET['wpfd_statistics_nonce']) ||
                !wp_verify_nonce($_GET['wpfd_statistics_nonce'], 'wpfd_statistics')) {
                wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
            }
        }

        $code = Utilities::getInput('code', 'GET', 'string');
        // User must login to generate/download this statistics
        $currentUser = wp_get_current_user();
        $wpUploadDir = wp_upload_dir();
        $csvExportDir = 'csv';

        if (!isset($currentUser->user_login)) {
            wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
        }
        /*
         * Filter to get wpfd upload folder
         * @param string Wpfd upload folder
         *
         * @ignore
         */
        $wpfdUploadDir = apply_filters('wpfd_upload_base_dir', 'wpfd');
        $wpfdDirname = $wpUploadDir['basedir'] . DIRECTORY_SEPARATOR . $wpfdUploadDir . DIRECTORY_SEPARATOR . $csvExportDir;
        $filePath = $wpfdDirname . DIRECTORY_SEPARATOR . $code . '.csv';
        $fileName = date('Y_m_d_H_i_s') .'_wpfd_statistics.csv';
        WpfdHelperFile::sendDownload($filePath, $fileName, 'csv');
        die();
    }
}
