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
use Joomunited\WPFramework\v1_0_5\Utilities;

defined('ABSPATH') || die();

/**
 * Class WpfdModelSearch
 */
class WpfdModelSearch extends Model
{
    /**
     * Search files
     *
     * @param array         $filters  Filters
     * @param boolean|false $doSearch Do search
     *
     * @return array
     */
    public function searchfile($filters = array(), $doSearch = false)
    {
        $app           = Application::getInstance('Wpfd');
        $path_WpfdBase = $app->getPath() . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'classes';
        $path_WpfdBase .= DIRECTORY_SEPARATOR . 'WpfdBase.php';
        require_once $path_WpfdBase;
        require_once $app->getPath() . '/site/helpers/class.exceltotext.php';
        require_once $app->getPath() . '/site/helpers/class.filetotext.php';
        require_once $app->getPath() . '/site/helpers/class.simplexlsx.php';

        $modelConfig     = $this->getInstance('configfront');
        $modelCategories = $this->getInstance('categoriesfront');
        $config          = $modelConfig->getGlobalConfig();
        $searchConfig    = $modelConfig->getSearchConfig();
        $dateFormat      = empty($config['date_format']) ? get_option('date_format') : $config['date_format'];
        $limit           = (int) Utilities::getInput('limit', 'POST', 'string');
        if (empty($limit)) {
            $limit = Utilities::getInt('limit', 'GET');
        }
        if (!isset($searchConfig['file_per_page'])) {
            $searchConfig['file_per_page'] = 15;
        }
        if ($limit > -1) { //-1: all
            $limit = ($limit === 0) ? (int) $searchConfig['file_per_page'] : $limit;
        }
        $args         = array(
            'posts_per_page' => -1,
            'post_type'      => 'wpfd_file',
        );
        $cloud_cond   = array();
        $cloud_cond[] = "mimeType != 'application/vnd.google-apps.folder' and trashed = false";
        if (isset($filters['q']) && $filters['q'] !== '') {
            // Document: https://developers.google.com/drive/api/v3/ref-search-terms
            $cloud_cond[] = "name contains '\"" . $filters['q'] . "\"'";
        }

        // Set relation for search files in catid and not in excluded categories
        $args['tax_query']['relation'] = 'AND';

        // Search within a category and it's children
        if (isset($filters['catid']) && $filters['catid'] !== '' && is_numeric($filters['catid'])) {
            $childCats = $modelCategories->getChildCategories($filters['catid']);
            $terms     = array();
            $terms[]   = $filters['catid'];
            foreach ($childCats as $cat) {
                $terms[] = $cat->term_id;
            }

            $args['tax_query'][] = array(
                'taxonomy' => 'wpfd-category',
                'field'    => 'term_id',
                'terms'    => $terms,
                'operator' => 'IN',
            );
        }

        // Exclude search from categories
        if (isset($filters['exclude']) && $filters['exclude'] !== '' && $filters['exclude'] !== '0') {
            $cleanExcluded = trim(preg_replace('/\s+/', '', $filters['exclude']));

            if (strpos($cleanExcluded, ',') === false) {
                $excludeIds = $cleanExcluded;
            } else {
                $excludeIds = explode(',', trim(preg_replace('/\s+/', '', $filters['exclude'])));
            }

            $args['tax_query'][] = array(
                'taxonomy' => 'wpfd-category',
                'field'    => 'term_id',
                'terms'    => $excludeIds,
                'operator' => 'NOT IN',
            );
        }

        if (isset($filters['ftags']) && $filters['ftags'] !== '') {
            $tags_tmp            = explode(',', $filters['ftags']);
            /**
             * Filter allow to change relation of tags
             *
             * @param string
             */
            $tagRelation = strtoupper(apply_filters('wpfd_search_tags_relation', 'OR'));
            if ($tagRelation === 'AND') {
                $tagsArgs = array(
                    'relation' => 'AND',
                );
                foreach ($tags_tmp as $tag) {
                    $tagsArgs[] = array(
                        'taxonomy' => 'wpfd-tag',
                        'field'    => 'name',
                        'terms'    => $tag,
                        'operator' => 'IN',
                    );
                }
            } else {
                $tagsArgs = array(
                    'relation' => $tagRelation,
                    array(
                        'taxonomy' => 'wpfd-tag',
                        'field'    => 'name',
                        'terms'    => $tags_tmp,
                        'operator' => 'IN',
                    )
                );
            }

            $args['tax_query'][] = $tagsArgs;
        }
        if (isset($filters['cfrom']) && $filters['cfrom'] !== '' && $filters['cto'] !== '') {
            $args['date_query'][] = array(
                'after'     => $this->getDate($dateFormat, $filters['cfrom'], true),
                'before'    => $this->getDate($dateFormat, $filters['cto']),
                'column'    => 'post_date',
                'inclusive' => true,
            );
        } else {
            if (isset($filters['cfrom']) && $filters['cfrom'] !== '') {
                $args['date_query'][] = array(
                    'after'  => $this->getDate($dateFormat, $filters['cfrom'], true),
                    'column' => 'post_date',
                );
            }

            if (isset($filters['cto']) && $filters['cto'] !== '') {
                $args['date_query'][] = array(
                    'before' => $this->getDate($dateFormat, $filters['cto']),
                    'column' => 'post_date',
                );
            }
        }
        if (isset($filters['ufrom']) && $filters['ufrom'] !== '' && $filters['uto'] !== '') {
            $args['date_query'][] = array(
                'after'     => $this->getDate($dateFormat, $filters['ufrom'], true),
                'before'    => $this->getDate($dateFormat, $filters['uto']),
                'column'    => 'post_modified',
                'inclusive' => true,
            );
            $cloud_cond_str       = " modifiedDate >= '" . $filters['ufrom'] . "' and modifiedDate <= '";
            $cloud_cond_str       .= $filters['uto'] . "'";
            $cloud_cond[]         = $cloud_cond_str;
        } else {
            if (isset($filters['ufrom']) && $filters['ufrom'] !== '') {
                $args['date_query'][] = array(
                    'after'  => $this->getDate($dateFormat, $filters['ufrom'], true),
                    'column' => 'post_modified',
                );
                $cloud_cond[]         = " modifiedDate >= '" . $filters['ufrom'] . "' ";
            }
            if (isset($filters['uto']) && $filters['uto'] !== '') {
                $args['date_query'][] = array(
                    'before' => $this->getDate($dateFormat, $filters['uto']),
                    'column' => 'post_modified',
                );
                $cloud_cond[]         = " modifiedDate <= '" . $filters['uto'] . "' ";
            }
        }
        $ordering = Utilities::getInput('ordering', 'POST', 'string');
        $dir      = Utilities::getInput('dir', 'POST', 'string');

        /**
         * Search results orderby
         *
         * @param string
         */
        $ordering = apply_filters('wpfd_search_results_orderby', $ordering);

        /**
         * Search results order direction
         *
         * @param string
         */
        $dir = apply_filters('wpfd_search_results_order', $dir);

        if ($dir !== '' && $ordering !== '' && in_array($ordering, array('type', 'title', 'created', 'updated', 'cat'))) {
            $args['orderby'] = $ordering === 'title' ? 'post_name' : $ordering;
            $args['order'] = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        } else {
            $args['orderby'] = 'post_name';
            $args['order'] = 'DESC';
        }

        $files = array();

        if (isset($filters['catid']) && is_numeric($filters['catid'])) {
            $files = $this->searchLocal($args, $filters, $searchConfig, $config);
        } elseif (isset($filters['catid']) && $filters['catid'] !== '' && is_string($filters['catid'])) {
            if (WpfdAddonHelper::getTermIdGoogleDriveByGoogleId($filters['catid'])) {
                if (has_filter('wpfdAddonSearchCloud', 'wpfdAddonSearchCloud')) {
                    /**
                     * Filters to search in google drive
                     *
                     * @param array Google search condition
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $files = apply_filters('wpfdAddonSearchCloud', $cloud_cond, $filters);
                }
            } elseif (WpfdAddonHelper::getTermIdOneDriveByOneDriveId($filters['catid'])) {
                if (has_filter('wpfdAddonSearchOneDrive', 'wpfdAddonSearchOneDrive')) {
                    /**
                     * Filters to search in onedrive
                     *
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $files = apply_filters('wpfdAddonSearchOneDrive', $filters);
                }
            } elseif (WpfdAddonHelper::getTermIdOneDriveBusinessByOneDriveId($filters['catid'])) {
                if (has_filter('wpfdAddonSearchOneDriveBusiness', 'wpfdAddonSearchOneDriveBusiness')) {
                    /**
                     * Filters to search in onedrive business
                     *
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $files = apply_filters('wpfdAddonSearchOneDriveBusiness', $filters);
                }
            } else {
                if (has_filter('wpfdAddonSearchDropbox', 'wpfdAddonSearchDropbox')) {
                    /**
                     * Filters to search in dropbox
                     *
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $files = apply_filters('wpfdAddonSearchDropbox', $filters);
                }
            }
            if (isset($filters['ftags']) && $filters['ftags'] !== '') {
                $results2 = array();
                $tags_tmp = explode(',', $filters['ftags']);
                foreach ($files as $k => $file) {
                    $file_tags = explode(',', $file->file_tags);
                    if (count(array_intersect($file_tags, $tags_tmp)) === count($tags_tmp)) {
                        $results2[] = $file;
                    }
                }
                $files = $results2;
            }
        } else {
            if ($doSearch) {
                $arr1 = array();
                $arr2 = array();
                $arr3 = array();
                $arr4 = array();
                if (has_filter('wpfdAddonSearchDropbox', 'wpfdAddonSearchDropbox')) {
                    /**
                     * Filters to search in dropbox
                     *
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $arr1 = apply_filters('wpfdAddonSearchDropbox', $filters);
                }
                if (has_filter('wpfdAddonSearchCloud', 'wpfdAddonSearchCloud')) {
                    /**
                     * Filters to search in google drive
                     *
                     * @param array Google search condition
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $arr2 = apply_filters('wpfdAddonSearchCloud', $cloud_cond, $filters);
                }
                if (has_filter('wpfdAddonSearchOneDrive', 'wpfdAddonSearchOneDrive')) {
                    /**
                     * Filters to search in onedrive
                     *
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $arr3 = apply_filters('wpfdAddonSearchOneDrive', $filters);
                }
                if (has_filter('wpfdAddonSearchOneDriveBusiness', 'wpfdAddonSearchOneDriveBusiness')) {
                    /**
                     * Filters to search in onedrive
                     *
                     * @param array Search condition
                     *
                     * @return array
                     *
                     * @internal
                     */
                    $arr4 = apply_filters('wpfdAddonSearchOneDriveBusiness', $filters);
                }
                $array1 = array_merge($arr1, $arr2, $arr3, $arr4);
                if (isset($filters['ftags']) && $filters['ftags'] !== '') {
                    $results2 = array();
                    $tags_tmp = explode(',', $filters['ftags']);
                    foreach ($array1 as $k => $file) {
                        $file_tags = explode(',', $file->file_tags);
                        if (count(array_intersect($file_tags, $tags_tmp)) === count($tags_tmp)) {
                            $results2[] = $file;
                        }
                    }
                    $array1 = $results2;
                }
                $array2 = $this->searchLocal($args, $filters, $searchConfig, $config);

                if (is_array($array1) && is_array($array2)) {
                    $files = array_merge($array1, $array2);
                } elseif (count($array1) > 0 && !is_array($array2)) {
                    $files = $array1;
                } elseif (!is_array($array1) && count($array2) > 0) {
                    $files = $array2;
                } else {
                    $files = array();
                }
            }
        }

        $files = $this->checkAccess($files);
        /**
         * Filters for search results
         *
         * @param array Files results
         * @param array Search filters
         *
         * @return array
         */
        $files = apply_filters('wpfd_search_results', $files, $filters);

        if (in_array($ordering, array('type', 'title', 'created', 'updated', 'cat'))) {
            switch ($ordering) {
                case 'type':
                    if ($dir === 'desc') {
                        usort($files, array('WpfdModelSearch', 'cmpTypeDesc'));
                    } else {
                        usort($files, array('WpfdModelSearch', 'cmpType'));
                    }
                    break;
                case 'created':
                    if ($dir === 'desc') {
                        usort($files, array('WpfdModelSearch', 'cmpCreatedDesc'));
                    } else {
                        usort($files, array('WpfdModelSearch', 'cmpCreated'));
                    }
                    break;
                case 'updated':
                    if ($dir === 'desc') {
                        usort($files, array('WpfdModelSearch', 'cmpModifiedDesc'));
                    } else {
                        usort($files, array('WpfdModelSearch', 'cmpModified'));
                    }
                    break;

                case 'cat':
                    if ($dir === 'desc') {
                        usort($files, array('WpfdModelSearch', 'cmpCatDesc'));
                    } else {
                        usort($files, array('WpfdModelSearch', 'cmpCat'));
                    }
                    break;
                case 'title':
                default:
                    if ($dir === 'desc') {
                        usort($files, array('WpfdModelSearch', 'cmpTitleDesc'));
                    } else {
                        usort($files, array('WpfdModelSearch', 'cmpTitle'));
                    }
                    break;
            }
        }

        if ($limit > 0) {
            $files = array_slice($files, 0, $limit);
        }

        return $files;
    }

    /**
     * Get date by format
     *
     * @param string  $format  Date format
     * @param string  $dateStr Date
     * @param boolean $from    Is start date?
     *
     * @return string
     */
    private function getDate($format, $dateStr, $from = false)
    {
        $date = date_create_from_format($format, $dateStr);
        $time = $from === false ? ' 23:59:59' : '00:00:00';
        if ($date) {
            return $date->format('Y/m/d') . $time;
        }

        return mysql2date('Y/m/d', $dateStr) . $time;
    }

    /**
     * Search local query
     *
     * @param array $args         Agruments
     * @param array $filters      Filters
     * @param array $searchConfig Search config
     * @param array $config       Config
     *
     * @return array
     */
    public function searchLocal($args, $filters, $searchConfig, $config)
    {
        if (isset($filters['catid']) && is_numeric($filters['catid'])) {
            // Check list ref file in category children
            $children = get_term_children($filters['catid'], 'wpfd-category');
            if (!empty($children) && !is_wp_error($children)) {
                $children[] = $filters['catid'];
            } else {
                $children = array($filters['catid']);
            }
            $multiCatFiles = array();

            foreach ($children as $childID) {
                // Get list file ref to this category
                $term = get_term($childID, 'wpfd-category');
                if (!is_wp_error($term)) {
                    $description   = json_decode($term->description, true);
                    if (!empty($description) && isset($description['refToFile'])) {
                        $refFiles = $description['refToFile'];
                    }

                    if (isset($refFiles) && count($refFiles)) {
                        foreach ($refFiles as $refCat => $refFileIds) {
                            foreach ($refFileIds as $refFileId) {
                                if (isset($filters['ftags']) && $filters['ftags'] !== '') {
                                    $searchTags = array_map('trim', explode(',', $filters['ftags']));
                                    $refFileTags = wp_get_object_terms($refFileId, 'wpfd-tag', array('fields' => 'all'));
                                    if (!is_wp_error($refFileTags) && !empty($refFileTags)) {
                                        foreach ($refFileTags as $fileTag) {
                                            if (in_array($fileTag->slug, $searchTags)) {
                                                $multiCatFiles[] = array('refCatId' => $childID, 'ID' => $refFileId);
                                            }
                                        }
                                    }
                                } else {
                                    $multiCatFiles[] = array('refCatId' => $childID, 'ID' => $refFileId);
                                }
                            }
                        }
                        unset($refFiles);
                    }
                }
            }
        }
        $newargs = array(
            'fields'         => '*',
            'post_status'    => 'published',
            'post_type'      => 'wpfd_file',
            'posts_per_page' => $args['posts_per_page']
        );
        if (isset($filters['q']) && $filters['q'] !== '') {
            $newargs['s'] = $filters['q'];
        }
        // Fix conflict plugin Go7 Pricing Table
        remove_all_filters('posts_fields');

        $wp_query = new WP_Query(array_merge($args, $newargs));

        $wp_query->is_search      = false;
        $wp_query->is_file_search = true;
        $posts                    = $wp_query->get_posts();

        $files                 = array();
        $viewer_type           = WpfdBase::loadValue($config, 'use_google_viewer', 'lightbox');
        $extension_viewer_list = 'png,jpg,pdf,ppt,pptx,doc,docx,xls,xlsx,dxf,ps,eps,xps,psd,tif,tiff,bmp,svg,pages,ai,dxf,ttf,txt,mp3,mp4';
        $extension_viewer      = explode(',', WpfdBase::loadValue($config, 'extension_viewer', $extension_viewer_list));
        $extension_viewer      = array_map('trim', $extension_viewer);
        $modelFile             = $this->getInstance('filefront');
        $modelTokens           = $this->getInstance('tokens');
        $modelCategory         = $this->getInstance('categoryfront');
        $rmdownloadext = (int) WpfdBase::loadValue($config, 'rmdownloadext', 1) === 1;

        $token       = $modelTokens->getOrCreateNew();
        $user        = wp_get_current_user();
        /**
         * Allow to change file length on search results
         *
         * @param integer
         */
        $cropTitle = apply_filters('wpfd_search_results_file_title_length', 0);
        foreach ($posts as $result) {
            $file = $modelFile->getFile($result->ID);
            if (!$file) {
                continue;
            }

            $remote_url = isset($file->remote_url) ? $file->remote_url : false;

            // Check access

            // Multi categories
            if (isset($filters['catid']) &&
                (int) $file->catid !== (int) $filters['catid'] &&
                property_exists($file, 'file_multi_category') &&
                is_array($file->file_multi_category) &&
                in_array($filters['catid'], $file->file_multi_category)
            ) {
                $category = $modelCategory->getCategory($filters['catid']);
                // Change cat title
                $file->cattitle = $category->name;
            } else {
                $category = $modelCategory->getCategory($file->catid);
            }

            if (empty($category) || is_wp_error($category)) {
                continue;
            }

            if ((int) $category->access === 1) {
                $roles = array();
                foreach ($user->roles as $role) {
                    $roles[] = strtolower($role);
                }
                $allows        = array_intersect($roles, $category->roles);
                $allows_single = false;

                if (isset($category->params['canview']) && $category->params['canview'] === '') {
                    $category->params['canview'] = 0;
                }

                if (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) &&
                    !count($category->roles)) {
                    if ((int) $category->params['canview'] === $user->ID) {
                        $allows_single = true;
                    }
                    if ($allows_single === false) {
                        continue;
                    }
                } elseif (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) &&
                          count($category->roles)) {
                    if ((int) $category->params['canview'] === $user->ID) {
                        $allows_single = true;
                    }
                    if (!($allows_single === true || !empty($allows))) {
                        continue;
                    }
                } else {
                    if (empty($allows)) {
                        continue;
                    }
                }
            }

            // Crop file title
            $file->crop_title = $result->post_title;
            if ($cropTitle && strlen($result->post_title) > $cropTitle) {
                $file->crop_title = substr($result->post_title, 0, $cropTitle) . '...';
            }
            $files[] = $file;
        }
        // Merge found files to current search result
        if (isset($multiCatFiles) && count($multiCatFiles)) {
            if (empty($config) || empty($config['uri'])) {
                $seo_uri = 'download';
            } else {
                $seo_uri = rawurlencode($config['uri']);
            }

            $perlink       = get_option('permalink_structure');
            $rewrite_rules = get_option('rewrite_rules');
            foreach ($multiCatFiles as $multiCatFile) {
                $file = $modelFile->getFile($multiCatFile['ID']);
                if (!$file) {
                    continue;
                }
                $category = $modelCategory->getCategory($multiCatFile['refCatId']);
                // Change cat title
                $file->cattitle = $category->name;
                $file->catname = $category->slug;
                $file->catid = $category->term_id;
                // Modify download link
                $file->seouri = $seo_uri;
                if (!empty($rewrite_rules)) {
                    if (strpos($perlink, 'index.php')) {
                        $linkdownload      = get_site_url() . '/index.php/' . $seo_uri . '/' . $file->catid . '/';
                        $linkdownload      .= $file->catname . '/' . $file->ID . '/' . $file->post_name;
                        $file->linkdownload = $linkdownload;
                    } else {
                        $linkdownload      = get_site_url() . '/' . $seo_uri . '/' . $file->catid . '/' . $file->catname;
                        $linkdownload      .= '/' . $file->ID . '/' . $file->post_name;
                        $file->linkdownload = $linkdownload;
                    }
                    if (isset($file->ext) && $file->ext && !$rmdownloadext) {
                        $file->linkdownload .= '.' . $file->ext;
                    };
                } else {
                    $linkdownload      = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=file.download';
                    $linkdownload      .= '&wpfd_category_id=' . $file->catid . '&wpfd_file_id=' . $file->ID;
                    $file->linkdownload = $linkdownload;
                }
                if (empty($category) || is_wp_error($category)) {
                    continue;
                }

                if ((int) $category->access === 1) {
                    $roles = array();
                    foreach ($user->roles as $role) {
                        $roles[] = strtolower($role);
                    }
                    $allows        = array_intersect($roles, $category->roles);
                    $allows_single = false;

                    if (isset($category->params['canview']) && $category->params['canview'] === '') {
                        $category->params['canview'] = 0;
                    }

                    if (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) &&
                        !count($category->roles)) {
                        if ((int) $category->params['canview'] === $user->ID) {
                            $allows_single = true;
                        }
                        if ($allows_single === false) {
                            continue;
                        }
                    } elseif (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) &&
                        count($category->roles)) {
                        if ((int) $category->params['canview'] === $user->ID) {
                            $allows_single = true;
                        }
                        if (!($allows_single === true || !empty($allows))) {
                            continue;
                        }
                    } else {
                        if (empty($allows)) {
                            continue;
                        }
                    }
                }

                // Crop file title
                $file->crop_title = $file->post_title;
                if ($cropTitle && strlen($file->post_title) > $cropTitle) {
                    $file->crop_title = substr($file->post_title, 0, $cropTitle) . '...';
                }

                // Search post
                if (isset($filters['q']) && ($this->strExists($file->post_title, $filters['q']) ||
                    $this->strExists($file->post_content, $filters['q']) ||
                    $this->strExists($file->post_excerpt, $filters['q']))) {
                    $files[] = $file;
                    continue;
                }
            }
        }
        return $files;
    }

    /**
     * Check access of files
     *
     * @param array $files List of file
     *
     * @return array
     */
    private function checkAccess($files)
    {
        if (is_array($files) && !empty($files)) {
            $modelCategory = $this->getInstance('categoryfront');
            $modelConfig   = $this->getInstance('configfront');

            $user          = wp_get_current_user();
            $user_id       = $user->ID;
            $params        = $modelConfig->getGlobalConfig();
            $results       = array();

            foreach ($files as $file) {
                //check access
                $category = $modelCategory->getCategory($file->catid);

                if (empty($category) || is_wp_error($category)) {
                    continue;
                }
                if (isset($file->state) && (int) $file->state === 0) {
                    continue;
                }
                $metaData = get_post_meta($file->ID, '_wpfd_file_metadata', true);
                if ((int) WpfdBase::loadValue($params, 'restrictfile', 0) === 1) {
                    $canview = isset($metaData['canview']) ? $metaData['canview'] : 0;
                    $canview = array_map('intval', explode(',', $canview));
                    if (!in_array($user_id, $canview) && !in_array(0, $canview)) {
                        continue;
                    }
                }
                if ((int) $category->access === 1) {
                    $roles = array();
                    foreach ($user->roles as $role) {
                        $roles[] = strtolower($role);
                    }
                    $allows = array_intersect($roles, $category->roles);
                    $allows_single = false;

                    if (isset($category->params['canview']) && $category->params['canview'] === '') {
                        $category->params['canview'] = 0;
                    }

                    if (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) &&
                        !count($category->roles)) {
                        if ((int) $category->params['canview'] === $user->ID) {
                            $allows_single = true;
                        }
                        if ($allows_single === false) {
                            continue;
                        }
                    } elseif (isset($category->params['canview']) && ((int) $category->params['canview'] !== 0) &&
                              count($category->roles)) {
                        if ((int) $category->params['canview'] === $user->ID) {
                            $allows_single = true;
                        }
                        if (!($allows_single === true || !empty($allows))) {
                            continue;
                        }
                    } else {
                        if (empty($allows)) {
                            continue;
                        }
                    }
                }
                $results[] = $file;
            }
            return $results;
        }
        return $files;
    }
    /**
     * Compare type
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpType($a, $b)
    {
        if (strtolower($a->ext) === strtolower($b->ext)) {
            return strcmp($a->post_name, $b->post_name);
        }

        return strcmp($a->ext, $b->ext);
    }

    /**
     * Compare type ordering DESC
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpTypeDesc($a, $b)
    {
        if (strtolower($a->ext) === strtolower($b->ext)) {
            return strcmp($a->title, $b->title);
        }

        return strcmp($b->ext, $a->ext);
    }

    /**
     *  Compare created
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpCreated($a, $b)
    {
        return ($a->created_time < $b->created_time) ? -1 : 1;
    }

    /**
     * Compare created ordering DESC
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpCreatedDesc($a, $b)
    {
        return ($a->created_time > $b->created_time) ? -1 : 1;
    }

    /**
     *  Compare modified
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpModified($a, $b)
    {
        return ($a->modified_time < $b->modified_time) ? -1 : 1;
    }

    /**
     * Compare modified ordering DESC
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpModifiedDesc($a, $b)
    {
        return ($a->modified_time > $b->created_time) ? -1 : 1;
    }

    /**
     * Compare category
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpCat($a, $b)
    {
        return strcmp($b->catname, $a->catname);
    }

    /**
     * Compare category ordering DESC
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpCatDesc($a, $b)
    {
        return strcmp($a->catname, $b->catname);
    }

    /**
     * Compare title
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpTitle($a, $b)
    {
        return strcmp($a->post_name, $b->post_name);
    }

    /**
     * Compare title ordering DESC
     *
     * @param object $a Object A
     * @param object $b Object B
     *
     * @return integer
     */
    private function cmpTitleDesc($a, $b)
    {
        return strcmp($b->post_name, $a->post_name);
    }

    /**
     * Search by attributes: title,description,content
     *
     * @param array  $files   Files
     * @param string $keyword Keyword
     *
     * @return array
     */
    private function getKeyPosts($files, $keyword)
    {
        $results  = array();
        $searchby = array('title' => 'title', 'description' => 'description', 'content' => 'content');
        foreach ($files as $file) {
            foreach ($searchby as $v) {
                if ($this->strExists(strtolower($file->$v), strtolower($keyword)) ||
                    $this->strExists(strtolower($keyword), strtolower($file->$v)) ||
                    strtolower($keyword) === strtolower($file->$v)
                ) {
                    $results[] = $file;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Compare string
     *
     * @param string $str    String
     * @param string $substr String to compare
     *
     * @return boolean
     */
    public function strExists($str, $substr)
    {
        if (($str !== null && $substr !== null && strpos(strtolower($str), strtolower($substr)) !== false)) {
            return true;
        } else {
            return false;
        }
    }
}
