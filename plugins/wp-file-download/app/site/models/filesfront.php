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
 * Class WpfdModelFilesfront
 */
class WpfdModelFilesfront extends Model
{

    /**
     * Get files by ordering
     *
     * @param integer $category     Category id
     * @param string  $ordering     Ordering
     * @param string  $ordering_dir Ordering direction
     * @param array   $listIdFiles  List id files
     * @param integer $refCatId     Ref cat id
     *
     * @return array
     */
    public function getFiles($category, $ordering = 'menu_order', $ordering_dir = 'ASC', $listIdFiles = array(), $refCatId = null)
    {
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
        $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $category);
        if (in_array($categoryFrom, wpfd_get_support_cloud())) {
            /**
             * Filters to get files from cloud
             *
             * @param integer Category id
             * @param array   List file id
             *
             * @internal
             *
             * @ignore
             *
             * @return array
             */
            $files = apply_filters('wpfd_addon_get_files', $category, $categoryFrom, $listIdFiles);
        } else {
            Application::getInstance('Wpfd');
            $modelCat    = $this->getInstance('categoryfront');
            $modelConfig = $this->getInstance('configfront');
            $modelTokens = $this->getInstance('tokens');

            $categorys   = $modelCat->getCategory($category);
            $params      = $modelConfig->getGlobalConfig();
            $user        = wp_get_current_user();
            $roles       = array();
            foreach ($user->roles as $role) {
                $roles[] = strtolower($role);
            }
            $rmdownloadext = (int) WpfdBase::loadValue($params, 'rmdownloadext', 1) === 1;

            $token = $modelTokens->getOrCreateNew();
            if ($ordering === 'ordering') {
                $ordering = 'menu_order';
            } elseif ($ordering === 'created_time') {
                $ordering = 'date';
            } elseif ($ordering === 'modified_time') {
                $ordering = 'modified';
            }

            $args    = array(
                'posts_per_page'   => -1,
                'post_type'        => 'wpfd_file',
                'orderby'          => $ordering,
                'order'            => $ordering_dir,
                'tax_query'        => array(
                    array(
                        'taxonomy'         => 'wpfd-category',
                        'terms'            => (int) $category,
                        'include_children' => false
                    )
                ),
                'suppress_filters' => false
            );
            // Fix conflict plugin Go7 Pricing Table
            remove_all_filters('posts_fields');
            remove_all_filters('pre_get_posts');
            $results = get_posts($args);
            $files   = array();

            $viewer_type           = WpfdBase::loadValue($params, 'use_google_viewer', 'lightbox');
            $extension_viewer_list = 'png,jpg,pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4';
            $extension_viewer      = explode(',', WpfdBase::loadValue($params, 'extension_viewer', $extension_viewer_list));
            $extension_viewer      = array_map('trim', $extension_viewer);
            $user                  = wp_get_current_user();
            $user_id               = $user->ID;
            $site_url              = get_site_url();
            $home_url              = get_home_url();
            $wpfd_lang             = '';
            global $sitepress;
            if (!empty($sitepress)) {
                $language_negotiation_type = $sitepress->get_setting('language_negotiation_type');
                if ((int) $language_negotiation_type === 1) {
                    /**
                     * Filters to get current language from WP Multi Lang
                     *
                     * @return string
                     *
                     * @ignore
                     */
                    $current_lang = apply_filters('wpml_current_language', null);
                    $default_lang = $sitepress->get_default_language();
                    $setting_urls = $sitepress->get_setting('urls');
                    if ((!empty($current_lang) && $current_lang !== $default_lang) || ($current_lang === $default_lang && !empty($setting_urls['directory_for_default_language']))) {
                        $wpfd_lang = '/' . $current_lang;
                        if ($site_url !== $home_url) {
                            $wpfd_lang = '';
                        }
                    }
                }
            }
            // Reduce query
            $ids = array();
            foreach ($results as $result) {
                if (!empty($listIdFiles) && is_array($listIdFiles)) {
                    if (!in_array($result->ID, $listIdFiles)) {
                        continue;
                    }
                }
                $ids[] = intval($result->ID);
            }
            $termLists = array();
            if (count($ids) > 0) {
                global $wpdb;
                $termListsQuery = 'SELECT tr.object_id AS ID, t.term_id, t.name, tt.parent, tt.count, tt.taxonomy
                                FROM ' . $wpdb->terms . ' AS t
                                INNER JOIN ' . $wpdb->term_taxonomy . ' AS tt
                                ON t.term_id = tt.term_id
                                INNER JOIN ' . $wpdb->term_relationships . ' AS tr
                                ON tr.term_taxonomy_id = tt.term_taxonomy_id
                                WHERE tt.taxonomy IN (\'wpfd-category\')
                                AND tr.object_id IN (' . implode(',', $ids) . ')
                                ORDER BY t.name ASC';

                $termLists = $wpdb->get_results($termListsQuery, OBJECT_K);
            }
            foreach ($results as $result) {
                if (!empty($listIdFiles) && is_array($listIdFiles)) {
                    if (!in_array($result->ID, $listIdFiles)) {
                        continue;
                    }
                }
                $ob             = new stdClass();
                $metaData       = get_post_meta($result->ID, '_wpfd_file_metadata', true);
                $productLinked  = get_post_meta($result->ID, '_wpfd_products_linked', true);
                if ((WpfdHelperFile::wpfdIsExpired((int)$result->ID) === true && $productLinked === '') ||
                    (WpfdHelperFile::wpfdIsExpired((int)$result->ID) === true && is_array($metaData)
                        && isset($metaData['woo_permission']) && $metaData['woo_permission'] === 'both_woo_and_wpfd_permission')) {
                    continue;
                }
                if ((int) WpfdBase::loadValue($params, 'restrictfile', 0) === 1) {
                    $canview = isset($metaData['canview']) ? $metaData['canview'] : 0;
                    $canview = array_map('intval', explode(',', $canview));
                    if (!in_array($user_id, $canview) && !in_array(0, $canview)) {
                        continue;
                    }
                }
                if (isset($metaData) && isset($metaData['remote_url'])) {
                    $remote_url = $metaData['remote_url'];
                } else {
                    $remote_url = false;
                }

                $ob->ID            = $result->ID;
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
                $ob->post_title    = apply_filters('wpfd_file_title', $result->post_title, $result->ID);
                $ob->post_name     = $result->post_name;
                $ob->ext           = isset($metaData['ext']) ? $metaData['ext'] : '';
                $ob->hits          = isset($metaData['hits']) ? (int) $metaData['hits'] : 0;
                $ob->versionNumber = isset($metaData['version']) ? $metaData['version'] : '';
                $ob->version       = '';
                $ob->description   = $result->post_excerpt;
                $ob->size          = isset($metaData['size']) ? $metaData['size'] : 0;
                $ob->created_time     = get_date_from_gmt($result->post_date_gmt);
                $ob->modified_time    = get_date_from_gmt($result->post_modified_gmt);
                $ob->created       = mysql2date(
                    WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
                    get_date_from_gmt($result->post_date_gmt)
                );
                $ob->modified      = mysql2date(
                    WpfdBase::loadValue($params, 'date_format', get_option('date_format')),
                    get_date_from_gmt($result->post_modified_gmt)
                );
                if (isset($termLists[$result->ID])) {
                    $wpfd_term = $termLists[$result->ID];
                    $ob->catname          = sanitize_title($wpfd_term->name);
                    $ob->cattitle         = $wpfd_term->name;
                    if (!is_null($refCatId)) {
                        $ob->catid = $refCatId;
                    } else {
                        $ob->catid = $wpfd_term->term_id;
                    }
                } else {
                    $ob->catname = '---';
                    $ob->cattitle = '---';
                    $ob->catid = 0;
                }


                $ob->file_custom_icon = isset($metaData['file_custom_icon']) && !empty($metaData['file_custom_icon']) ?
                    $site_url . $metaData['file_custom_icon'] : '';

                if ($viewer_type !== 'no' &&
                    in_array(strtolower($ob->ext), $extension_viewer)
                    && ($remote_url === false)) {
                    $ob->viewer_type = $viewer_type;
                    $ob->viewerlink  = WpfdHelperFile::isMediaFile($ob->ext) ?
                        WpfdHelperFile::getMediaViewerUrl(
                            $result->ID,
                            $ob->catid,
                            $ob->ext
                        ) : WpfdHelperFile::getViewerUrl($result->ID, $ob->catid, $token);
                }

                $open_pdf_in = WpfdBase::loadValue($params, 'open_pdf_in', 0);

                if ((int) $open_pdf_in === 1 && strtolower($ob->ext) === 'pdf') {
                    $ob->openpdflink = WpfdHelperFile::getPdfUrl($result->ID, $ob->catid, $token) . '&preview=1';
                }
                $config = get_option('_wpfd_global_config');
                if (empty($config) || empty($config['uri'])) {
                    $seo_uri = 'download';
                } else {
                    $seo_uri = rawurlencode($config['uri']);
                }
                $ob->seouri    = $seo_uri;
                $perlink       = get_option('permalink_structure');
                $rewrite_rules = get_option('rewrite_rules');

                if (wpfd_can_download_files()) {
                    if (!empty($rewrite_rules)) {
                        if (strpos($perlink, 'index.php')) {
                            $linkdownload     = $site_url . $wpfd_lang . '/index.php/' . $seo_uri . '/' . $ob->catid;
                            $linkdownload     .= '/' . $ob->catname . '/' . $result->ID . '/' . $result->post_name;
                            $ob->linkdownload = $linkdownload;
                        } else {
                            $linkdownload     = $site_url . $wpfd_lang . '/' . $seo_uri . '/' . $ob->catid . '/' . $ob->catname;
                            $linkdownload     .= '/' . $result->ID . '/' . $result->post_name;
                            $ob->linkdownload = $linkdownload;
                        }
                        if ($ob->ext && !$rmdownloadext) {
                            $ob->linkdownload .= '.' . $ob->ext;
                        }
                    } else {
                        $linkdownload     = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=file.download';
                        $linkdownload     .= '&wpfd_category_id=' . $ob->catid . '&wpfd_file_id=' . $result->ID;
                        $ob->linkdownload = $linkdownload;
                    }
                } else {
                    $ob->linkdownload = '';
                }

                // Crop file titles
                $ob->crop_title = WpfdBase::cropTitle($categorys->params, $categorys->params['theme'], $result->post_title);
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
                $ob->crop_title = apply_filters('wpfd_file_title', $ob->crop_title, $result->ID);


                $files[]        = $ob;
            }
            wp_reset_postdata();
        }
        /**
         * Filter files info in front
         *
         * @param array Files object
         *
         * @return object
         *
         * @ignore
         */
        $files = apply_filters('wpfd_files_info', $files);
        $reverse = strtoupper($ordering_dir) === 'DESC' ? true : false;

        if ($ordering === 'size') {
            $files = wpfd_sort_by_property($files, 'size', 'ID', $reverse);
        } elseif ($ordering === 'version') {
            $files = wpfd_sort_by_property($files, 'version', 'ID', $reverse);
        } elseif ($ordering === 'hits') {
            $files = wpfd_sort_by_property($files, 'hits', 'ID', $reverse);
        } elseif ($ordering === 'ext') {
            $files = wpfd_sort_by_property($files, 'ext', 'ID', $reverse);
        } elseif ($ordering === 'description') {
            $files = wpfd_sort_by_property($files, 'description', 'ID', $reverse);
        }

        return $files;
    }

    /**
     * Get files all categories
     *
     * @param string $ordering     Ordering
     * @param string $ordering_dir Ordering direction
     *
     * @return array
     */
    public function getFilesAllCat($ordering = 'menu_order', $ordering_dir = 'ASC')
    {
        Application::getInstance('Wpfd');
        /* @var WpfdModelCategories $categoriesModel */
        $categoriesModel = self::getInstance('categoriesfront');

        $categories = $categoriesModel->getLevelCategories();
        $files = array();
        // Check access and get files
        foreach ($categories as $category) {
            if (WpfdHelper::checkCategoryAccess($category)) {
                // Get files
                $categoryFiles = $this->getFiles($category->term_id, $ordering, $ordering_dir);
                $files = array_merge($files, $categoryFiles);
            }
        }
        $reverse = strtoupper($ordering_dir) === 'DESC' ? true : false;

        if ($ordering === 'size') {
            $files = wpfd_sort_by_property($files, 'size', 'ID', $reverse);
        } elseif ($ordering === 'version') {
            $files = wpfd_sort_by_property($files, 'version', 'ID', $reverse);
        } elseif ($ordering === 'hits') {
            $files = wpfd_sort_by_property($files, 'hits', 'ID', $reverse);
        } elseif ($ordering === 'ext') {
            $files = wpfd_sort_by_property($files, 'ext', 'ID', $reverse);
        } elseif ($ordering === 'description') {
            $files = wpfd_sort_by_property($files, 'description', 'ID', $reverse);
        }

        return $files;
    }
}
