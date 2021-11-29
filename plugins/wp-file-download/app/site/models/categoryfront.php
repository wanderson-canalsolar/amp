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
 * Class WpfdModelCategoryfront
 */
class WpfdModelCategoryfront extends Model
{

    /**
     * Get a category info
     *
     * @param integer $id     Category id
     * @param integer $rootId Root category id
     *
     * @return boolean
     */
    public function getCategory($id, $rootId = 0)
    {
        $result = get_term($id, 'wpfd-category');
        Application::getInstance('Wpfd', __FILE__);
        $modelConfig = $this->getInstance('configfront');
        $main_config = $modelConfig->getGlobalConfig();

        if (!empty($result) && !is_wp_error($result)) {
            $result->name = html_entity_decode($result->name);
            if ($result->description === 'null' || $result->description === '') {
                $result->params = array();
            } else {
                $result->params = json_decode($result->description, true);
            }

            if (!isset($result->params['theme'])) {
                $result->params['theme'] = $main_config['defaultthemepercategory'];
            }
            if (!isset($result->params['category_own'])) {
                $categoryOwn = get_current_user_id();
                $result->params['category_own'] = $categoryOwn;
            } else {
                $categoryOwn = $result->params['category_own'];
            }

            $defaultParams = array(
                'order' => 'desc',
                'orderby' => 'title'
            );
            /**
             * Filters allow setup default params for new category
             *
             * @param array Default values: order, orderby
             *
             * @ignore
             *
             * @return array
             */
            $defaultParams = apply_filters('wpfd_default_category_params', $defaultParams);

            $ordering    = isset($result->params['ordering']) ? $result->params['ordering'] : $defaultParams['orderby'];
            $orderingdir = isset($result->params['orderingdir']) ? $result->params['orderingdir'] : $defaultParams['order'];

            if ((int) $main_config['catparameters'] === 0) {
                $result->params                 = array_merge(
                    $result->params,
                    $modelConfig->getConfig($main_config['defaultthemepercategory'])
                );
                $result->params['theme']        = $main_config['defaultthemepercategory'];
                $result->params['category_own'] = $categoryOwn;
            }
            if (!empty($result->parent)) {
                $parentCat = get_term($result->parent, 'wpfd-category');
                if (!empty($parentCat) && !is_wp_error($parentCat)) {
                    $result->parent_title = $parentCat->name;
                }
            }
            $categoryVisibility = wpfd_get_category_visibility($id);

            $result->roles       = isset($categoryVisibility['roles']) ? $categoryVisibility['roles'] : array();
            $result->access      = isset($categoryVisibility['access']) ? $categoryVisibility['access'] : 0;
            $result->ordering         = $ordering;
            $result->orderingdir      = $orderingdir;
            $result->linkdownload_cat = $this->urlBtnDownloadCat($result->term_id, sanitize_title($result->name));
            if ($result->term_id !== $rootId) {
                $result->breadcrumbs = $this->generateBreadcrumb($result->term_id, $rootId) . '<li><span>' . $result->name . '</span></li>';
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Generate breadcrumb
     *
     * @param integer $catId  Category Id
     * @param integer $rootId Root category id
     *
     * @return string
     */
    public function generateBreadcrumb($catId, $rootId)
    {
        $output  = '';
        $term    = get_term_by('id', $catId, 'wpfd-category');

        // Create a list of all the term's parents
        $parent = $term->parent;
        while ($parent) {
            $parents[] = $parent;
            if ($parent === $rootId) {
                break;
            }
            $new_parent = get_term_by('id', $parent, 'wpfd-category');
            $parent     = $new_parent->parent;
        }

        if (!empty($parents)) {
            $parents = array_reverse($parents);

            // For each parent, create a breadcrumb item
            foreach ($parents as $parent) {
                $item   = get_term_by('id', $parent, 'wpfd-category');
                $output .= '<li><a class="catlink" data-idcat="' . $item->term_id . '" href="javascript:void(0);">' . $item->name . '</a><span class="divider"> &gt; </span></li>';
            }
        }

        return $output;
    }
    /**
     * Get url download cat
     *
     * @param integer $catid   Category id
     * @param string  $catname Category name
     *
     * @return string
     */
    public function urlBtnDownloadCat($catid, $catname)
    {
        $perlink       = get_option('permalink_structure');
        $config        = get_option('_wpfd_global_config');
        $rewrite_rules = get_option('rewrite_rules');

        if (empty($config) || empty($config['uri'])) {
            $seo_uri = 'download/wpfdcat';
        } else {
            $seo_uri = rawurlencode($config['uri']) . '/wpfdcat';
        }

        if (!empty($rewrite_rules)) {
            if (strpos($perlink, 'index.php')) {
                $linkdownloadCat = get_site_url() . '/index.php/' . $seo_uri . '/' . $catid . '/' . $catname;
            } else {
                $linkdownloadCat = get_site_url() . '/' . $seo_uri . '/' . $catid . '/' . $catname;
            }
        } else {
            $linkdownloadCat = admin_url('admin-ajax.php') . '?juwpfisadmin=false&action=wpfd&task=files.download';
            $linkdownloadCat .= '&wpfd_category_id=' . $catid . '&wpfd_cat_name=' . $catname;
        }

        return $linkdownloadCat;
    }
}
