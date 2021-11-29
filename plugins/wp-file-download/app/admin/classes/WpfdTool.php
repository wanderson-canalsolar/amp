<?php

/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0
 */

// no direct access
defined('ABSPATH') || die();

use Joomunited\WPFramework\v1_0_5\Application;

/**
 * Class WpfdTool
 */
class WpfdTool
{
    /**
     * Show error message
     *
     * @return void
     */
    public function wpfdImportNotice()
    {
        global $wpdb;
        $wpfd_categories_var = $wpdb->get_var("SHOW TABLES LIKE '" . $wpdb->prefix . "wpfd_categories'");
        if ($wpfd_categories_var === $wpdb->prefix . 'wpfd_categories') {
            $securityCode = wp_create_nonce('wpfd_import');
            $echojs = '<script type="text/javascript">' . PHP_EOL;
            $echojs .= 'function importWpfdTaxonomy(doit,button){' . PHP_EOL;
            $echojs .= 'jQuery(button).find(".spinner").show().css({"visibility":"visible"});' . PHP_EOL;
            $echojs .= 'jQuery.post(wpfdajaxurl, {action: "wpfd_import",doit:doit,security: ';
            $echojs .= $securityCode . '}, function(response) {' . PHP_EOL;
            $echojs .= 'jQuery(button).closest("div#wpfd_error").hide();' . PHP_EOL;
            $echojs .= 'if(doit===true){' . PHP_EOL;
            $echojs .= 'jQuery("#wpfd_error").after("<div class=\'updated\'> <p><strong>';
            $echojs .= esc_html__('Categories imported into taxonomies. Enjoy!!!', 'wpfd') . '</strong></p></div>");' . PHP_EOL;
            $echojs .= '}' . PHP_EOL;
            $echojs .= 'window.location.reload(true);' . PHP_EOL;
            $echojs .= '});' . PHP_EOL;
            $echojs .= '}' . PHP_EOL;
            $echojs .= '</script>';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print only
            echo $echojs;
            $wpfd_error = '<div class="error" id="wpfd_error">';
            $wpfd_error .= '<p>';
            $wpfd_error .= esc_html__('You\'ve just installed new version WP File Download, You can import your categories into taxonomies', 'wpfd');
            $wpfd_error .= '<a href="#" class="button button-primary" style="margin: 0 5px;"';
            $wpfd_error .= 'onclick="importWpfdTaxonomy(true,this);" id="wpfdImportBtn">';
            $wpfd_error .= esc_html__('Import categories now', 'wpfd');
            $wpfd_error .= ' <span class="spinner" style="display:none"></span></a> or ';
            $wpfd_error .= ' <a href="#" onclick="importWpfdTaxonomy(false,this);" style="margin: 0 5px;"';
            $wpfd_error .= 'class="button">' . esc_html__('No thanks ', 'wpfd');
            $wpfd_error .= ' <span class="spinner" style="display:none"></span></a>';
            $wpfd_error .= '</p>';
            $wpfd_error .= '</div>';
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Print only
            echo $wpfd_error;
        } else {
            self::createCategoryIfNoneExist();
        }
    }

    /**
     * Import categories
     *
     * @return boolean|void
     */
    public static function wpfdImportCategories()
    {

        check_admin_referer('wpfd_import', 'security');

        $option_import_taxo = get_option('_wpfd_import_notice_flag');
        if (isset($option_import_taxo) && $option_import_taxo === 'yes') {
            die();
        }

        if ($_POST['doit'] === 'true') {
            $app = Application::getInstance('Wpfd');
            $path_categories = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'models';
            $path_categories .= DIRECTORY_SEPARATOR . 'categories.php';
            require_once $path_categories;
            $modelCats = new WpfdModelCategories();
            $categories = $modelCats->getCategoriesOld();
            if (!$categories) {
                if ($_POST['doit'] === 'true') {
                    update_option('_wpfd_import_notice_flag', 'yes');
                } else {
                    update_option('_wpfd_import_notice_flag', 'no');
                }
                die();
            }
            $path_category = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'models';
            $path_category .= DIRECTORY_SEPARATOR . 'category.php';
            require_once $path_category;
            $modelCat = new WpfdModelCategory();
            $termsRel = array('0' => 0);
            foreach ($categories as $category) {
                $inserted = wp_insert_term(
                    $category->title,
                    'wpfd-category',
                    array('slug' => sanitize_title($category->title))
                );
                if (is_wp_error($inserted)) {
                    //try again
                    $inserted = wp_insert_term(
                        $category->title,
                        'wpfd-category',
                        array('slug' => sanitize_title($category->title) . '-' . time())
                    );
                    if (is_wp_error($inserted)) {
                        wp_send_json($inserted->get_error_message());
                    }
                }

                $modelCat->updateTermOrder($inserted['term_id'], $category->lft);

                $termsRel[$category->id] = $inserted['term_id'];
            }
            foreach ($categories as $category) {
                wp_update_term(
                    $termsRel[$category->id],
                    'wpfd-category',
                    array('parent' => $termsRel[$category->parent_id])
                );
            }

            //update files to attachments
            global $wpdb;
            $query = 'SELECT f.* FROM ' . $wpdb->prefix . 'wpfd_files as f ORDER BY f.ordering ASC';
            // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- Select query without input
            $result = $wpdb->query($query);
            if ($result === false) {
                return false;
            }
            // phpcs:ignore WordPress.Security.EscapeOutput.NotPrepared -- Select query without input
            $files = stripslashes_deep($wpdb->get_results($query, OBJECT));
            // Get the path to the upload directory.
            $wp_upload_dir = wp_upload_dir();

            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            foreach ($files as $file) {
                $filename = $wp_upload_dir['basedir'] . '/wpfd/' . $file->catid . '/' . $file->file;
                //move file to new term_id: $termsRel[$file->catid]
                if (file_exists($filename)) {
                    $file_dir = WpfdBase::getFilesPath($termsRel[$file->catid]);
                    if (!file_exists($file_dir)) {
                        mkdir($file_dir, 0777, true);
                        $data = '<html><body bgcolor="#FFFFFF"></body></html>';
                        $tmpfile = fopen($file_dir . 'index.html', 'w');
                        fwrite($tmpfile, $data);
                        fclose($tmpfile);
                        $data = 'deny from all';
                        $tmpfile = fopen($file_dir . '.htaccess', 'w');
                        fwrite($tmpfile, $data);
                        fclose($tmpfile);
                    }
                    $newFile = $wp_upload_dir['basedir'] . '/wpfd/' . $termsRel[$file->catid] . '/' . $file->file;
                    copy($filename, $newFile);
                    $filename = $newFile;
                }

                // Check the type of file. We'll use this as the 'post_mime_type'.
                $filetype = wp_check_filetype(basename($filename), null);
                $post_title = $file->title;
                if (empty($post_title)) {
                    $post_title = preg_replace('/\.[^.]+$/', '', basename($filename));
                }
                // Prepare an array of post data for the attachment.
                $baseguid = $wp_upload_dir['baseurl'] . '/wpfd/' . $termsRel[$file->catid] . '/' . basename($filename);
                $attachment = array(
                    'guid' => $baseguid,
                    'post_type' => 'wpfd_file',
                    'post_mime_type' => $filetype['type'],
                    'post_title' => $post_title,
                    'post_excerpt' => $file->description,
                    'post_content' => '',
                    'post_status' => 'publish',
                    'menu_order' => $file->ordering
                );
                $attach_id = wp_insert_post($attachment);
                if ($attach_id) {
                    // Generate the metadata for the attachment, and update the database record.
                    //$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
                    //wp_update_attachment_metadata( $attach_id, $attach_data );

                    $metadata = array();
                    $metadata['ext'] = $file->ext;
                    $metadata['size'] = $file->size;
                    $metadata['hits'] = $file->hits;
                    $metadata['version'] = $file->version;
                    $metadata['file'] = $file->file;
                    update_post_meta($attach_id, '_wpfd_file_metadata', $metadata);

                    $termsArray = array();
                    $termsArray[] = $termsRel[$file->catid];
                    wp_set_post_terms($attach_id, $termsArray, 'wpfd-category');
                }
            }
        } else { //if there isn't any categories then create one
            self::createCategoryIfNoneExist();
        }

        if ($_POST['doit'] === 'true') {
            update_option('_wpfd_import_notice_flag', 'yes');
        } else {
            update_option('_wpfd_import_notice_flag', 'no');
        }
        die();
    }

    /**
     * Create category if not exist
     *
     * @return void
     */
    public static function createCategoryIfNoneExist()
    {
        $app = Application::getInstance('Wpfd');
        $path_categories = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'models';
        $path_categories .= DIRECTORY_SEPARATOR . 'categories.php';
        require_once $path_categories;
        $modelCats = new WpfdModelCategories();
        $cats = $modelCats->getSubCategories(0);
        if (count($cats) === 0) { //if there isn't any categories then create one
            $path_category = $app->getPath() . DIRECTORY_SEPARATOR . $app->getType() . DIRECTORY_SEPARATOR . 'models';
            $path_category .= DIRECTORY_SEPARATOR . 'category.php';
            require_once $path_category;
            $modelCat = new WpfdModelCategory();
            $modelCat->addCategory(esc_html__('New category', 'wpfd'));
        }
    }

    /**
     * Delete all data
     *
     * @return void
     */
    public function deleteAllData()
    {
        $taxonomy = 'wpfd-category';
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'orderby' => 'term_group',
            'hierarchical' => true,
            'hide_empty' => false
        ));
        $count = count($terms);
        if ($count > 0) {
            foreach ($terms as $term) {
                wp_delete_term($term->term_id, $taxonomy);
            }
        }

        //delete posts and meta key
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'wpfd_file',
            'orderby' => 'menu_order',
            'order' => 'ASC'
        );
        $results = get_posts($args);
        if (count($results) > 0) {
            foreach ($results as $result) {
                // Delete's each post.
                wp_delete_post($result->ID, true);
                delete_post_meta($result->ID, '_wpfd_file_metadata');
            }
        }
    }

    /**
     * Parse Size
     *
     * @param integer $size Site input
     *
     * @return float
     */
    public static function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
            // Find the position of the unit in the ordered string which is
            // the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * Remove recursively a directory
     *
     * @param string $dir Directory path to remove
     *
     * @return void
     */
    public static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        self::rrmdir($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }
}
