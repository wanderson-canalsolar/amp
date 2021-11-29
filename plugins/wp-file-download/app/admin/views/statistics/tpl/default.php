<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0W
 */


use Joomunited\WPFramework\v1_0_5\Application;
use Joomunited\WPFramework\v1_0_5\Utilities;

// No direct access.
defined('ABSPATH') || die();
// Checking for nonce here
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['wpfd_statistics_nonce']) ||
        !wp_verify_nonce($_POST['wpfd_statistics_nonce'], 'wpfd_statistics')) {
        wp_die(esc_html__('You don\'t have permission to perform this action!', 'wpfd'));
    }
}
?>
<div id="wpfd-statistics" class="wpfd-statistics">
    <h2>Download Statistics</h2>
    <form action="" class="wpfd-statistics--form" id="wpfd-statistics-form" name="wpfd-statistics-form" method="post">
        <?php wp_nonce_field('wpfd_statistics', 'wpfd_statistics_nonce'); ?>
        <?php include_once WPFD_PLUGIN_DIR_PATH . 'app/admin/views/statistics/tpl/filters.php'; ?>
        <?php include_once WPFD_PLUGIN_DIR_PATH . 'app/admin/views/statistics/tpl/chart.php'; ?>
        <?php include_once WPFD_PLUGIN_DIR_PATH . 'app/admin/views/statistics/tpl/table.php'; ?>
    </form>
</div>
