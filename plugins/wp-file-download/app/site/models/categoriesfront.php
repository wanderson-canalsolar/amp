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
 * Class WpfdModelCategoriesfront
 */
class WpfdModelCategoriesfront extends Model
{
    /**
     * Get all categories by parent category
     *
     * @param integer $idcategory Category id
     *
     * @return array
     */
    public function getCategories($idcategory)
    {
        $user = wp_get_current_user();
        $roles = array();
        foreach ($user->roles as $role) {
            $roles[] = strtolower($role);
        }
        $result = array();
        /**
         * Filters allow to change ordering direction of categories
         *
         * @param string
         *
         * @ignore
         *
         * @return string
         */
        $orderDirection = apply_filters('wpfd_categories_order', 'asc');

        /**
         * Filters allow to change order column of categories
         *
         * @param string
         *
         * @ignore
         *
         * @return string
         */
        $orderBy = apply_filters('wpfd_categories_orderby', 'term_group');
        $categories = get_terms(array(
            'taxonomy'     => 'wpfd-category',
            'orderby'      => $orderBy,
            'order'        => $orderDirection,
            'hierarchical' => 1,
            'hide_empty'   => 0,
            'parent'       => $idcategory
        ));

        if ($categories) {
            Application::getInstance('Wpfd');
            $configModel = Model::getInstance('configfront');
            $config      = $configModel->getGlobalConfig();

            foreach ($categories as $category) {
                $emptyChild = false;
                $children = get_term_children($category->term_id, 'wpfd-category');
                if (is_wp_error($children) || (is_array($children) && empty($children))) {
                    $emptyChild = true;
                }

                // Check multiple categories file
                $description = json_decode($category->description, true);
                if (!empty($description) && isset($description['refToFile']) && is_array($description['refToFile']) && !empty($description['refToFile'])) {
                    $emptyChild = false;
                }
                $the_query = new WP_Query(array(
                    'post_type' => 'wpfd_file',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'wpfd-category',
                            'field' => 'id',
                            'terms' => $category->term_id
                        )
                    )
                ));

                $fileInCategory = $the_query->found_posts;
                if ($emptyChild && intval($fileInCategory) === 0 && is_array($config) && isset($config['show_empty_folder']) && intval($config['show_empty_folder']) === 0) {
                    // Check is cloud category?
                    $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $category->term_id);
                    if (!in_array($categoryFrom, wpfd_get_support_cloud())) {
                        continue;
                    }
                }
                $category->name = html_entity_decode($category->name);
                $categoryVisibility = wpfd_get_category_visibility($category->term_id);
                $cat_roles       = isset($categoryVisibility['roles']) ? $categoryVisibility['roles'] : array();
                $cat_access      = isset($categoryVisibility['access']) ? $categoryVisibility['access'] : 0;
                $params         = json_decode($category->description, true);
                $allows_single  = false;

                if (isset($params['canview']) && $params['canview'] !== '') {
                    if (((int)$params['canview'] !== 0) && (int) $params['canview'] === $user->ID) {
                        $allows_single = true;
                    }
                }

                if ((int) $cat_access === 1) {
                    $allows = array_intersect($roles, $cat_roles);
                    if ($allows || $allows_single) {
                        $result[] = $category;
                    }
                } else {
                    $result[] = $category;
                }
            }
        }
        if ($orderBy === 'term_group') {
            usort($result, function ($a, $b) {
                return ($a->term_group < $b->term_group) ? -1 : 1;
            });
        }
        /**
         * Allow to filter the front categories list
         *
         * @param array
         */
        $result = apply_filters('wpfd_categories', $result);

        return stripslashes_deep($result);
    }

    /**
     * Count sub categories
     *
     * @param integer $idcategory Parent category id
     *
     * @return array|integer|WP_Error
     */
    public function getSubCategoriesCount($idcategory)
    {
        $count = wp_count_terms(
            'wpfd-category',
            'orderby=term_group&hierarchical=1&hide_empty=0&parent=' . $idcategory
        );
        return $count;
    }

    /**
     * Get level categories
     *
     * @param integer $rootCategory Root category to get Level categories
     *
     * @return array
     */
    public function getLevelCategories($rootCategory = 0)
    {
        $results       = array();
        if ($rootCategory !== 0) {
            $root = get_term($rootCategory, 'wpfd-category');
        } else {
            $root          = new stdClass();
            $root->level   = 0;
            $root->term_id = 0;
        }

        $this->getCategoriesRecursive($root, $results);

        $user  = wp_get_current_user();
        $roles = array();
        foreach ($user->roles as $role) {
            $roles[] = strtolower($role);
        }
        if ($results) {
            foreach ($results as $key => $category) {
                $cat = get_term($category->term_id, 'wpfd-category');
                $params = array();
                if ($cat->description !== '') {
                    $params = json_decode($cat->description, true);
                }

                $categoryVisibility = wpfd_get_category_visibility($category->term_id);
                $cat_roles       = isset($categoryVisibility['roles']) ? $categoryVisibility['roles'] : array();
                $cat_access      = isset($categoryVisibility['access']) ? $categoryVisibility['access'] : 0;
                if (isset($params['canview']) && ($params['canview'] === '')) {
                    $params['canview'] = 0;
                }
                if ((int) $cat_access === 1) {
                    $allows = array_intersect($roles, $cat_roles);
                    // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                    if (isset($params['canview']) && (int) $params['canview'] !== 0 && is_countable($cat_roles) && !count($cat_roles)) {
                        if ((int) $params['canview'] !== $user->ID) {
                            unset($results[$key]);
                            continue;
                        }
                    } elseif (isset($params['canview']) && (int) $params['canview'] !== 0 && is_countable($cat_roles) && count($cat_roles)) { // phpcs:ignore PHPCompatibility.FunctionUse.NewFunctions.is_countableFound -- is_countable() was declared in functions.php
                        if (!((int)$params['canview'] === $user->ID || !empty($allows))) {
                            unset($results[$key]);
                            continue;
                        }
                    } else {
                        if (empty($allows)) {
                            unset($results[$key]);
                            continue;
                        }
                    }
                }
                $results[$key] = apply_filters('wpfd_level_category', $category);
                $results[$key] = apply_filters('wpfd_level_category_dropbox', $category);
                $results[$key] = apply_filters('wpfd_level_category_onedrive', $category);
                $results[$key] = apply_filters('wpfd_level_category_onedrive_business', $category);
            }
        }

        return array_values($results);
    }

    /**
     * Get categories recursive
     *
     * @param object $cat     Category
     * @param array  $results Results
     *
     * @return void
     */
    public function getCategoriesRecursive($cat, &$results)
    {
        if (!is_array($results)) {
            $results = array();
        }

        /**
         * Filters allow to change ordering direction of categories
         *
         * @param string
         *
         * @ignore
         *
         * @return string
         */
        $orderDirection = apply_filters('wpfd_categories_order', 'asc');

        /**
         * Filters allow to change order column of categories
         *
         * @param string
         *
         * @ignore
         *
         * @return string
         */
        $orderBy = apply_filters('wpfd_categories_orderby', 'term_group');

        $categories = get_terms(array(
            'taxonomy'     => 'wpfd-category',
            'orderby'      => $orderBy,
            'order'        => $orderDirection,
            'hierarchical' => 1,
            'hide_empty'   => 0,
            'parent'       => $cat->term_id
        ));

        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category->level = $cat->level + 1;
                $results[] = $category;
                $this->getCategoriesRecursive($category, $results);
            }
        }
    }

    /**
     * Get child categories
     *
     * @param integer $catid Categoryid
     *
     * @return array
     */
    public function getChildCategories($catid)
    {
        $results = array();

        /**
         * Filters allow to change ordering direction of categories
         *
         * @param string
         *
         * @ignore
         *
         * @return string
         */
        $orderDirection = apply_filters('wpfd_categories_order', 'asc');

        /**
         * Filters allow to change order column of categories
         *
         * @param string
         *
         * @ignore
         *
         * @return string
         */
        $orderBy = apply_filters('wpfd_categories_orderby', 'term_group');

        $categories = get_terms(array(
            'taxonomy'     => 'wpfd-category',
            'orderby'      => $orderBy,
            'order'        => $orderDirection,
            'hierarchical' => 1,
            'hide_empty'   => 0,
            'parent'       => $catid
        ));

        if ($categories) {
            foreach ($categories as $category) {
                $results[] = $category;
                $this->getCategoriesRecursive($category, $results);
            }
        }
        return $results;
    }

    /**
     * Get all parents categories
     *
     * @param integer $catid      Categoryid
     * @param integer $displaycat Display cat
     *
     * @return array
     */
    public function getParentsCat($catid, $displaycat)
    {
        $results = array();
        $results[] = $catid;
        $this->getParentCat($catid, $results, $displaycat);
        return $results;
    }

    /**
     * Get parents categories
     *
     * @param integer $catid      Categoryid
     * @param array   $results    Results
     * @param integer $displaycat Display cat
     *
     * @return void
     */
    public function getParentCat($catid, &$results, $displaycat)
    {

        if ((int) $catid !== 0) {
            $cat = get_term($catid, 'wpfd-category');
            if ($cat->parent !== 0 && $cat->parent !== (int) $displaycat) {
                $results[] = $cat->parent;
                $this->getParentCat($cat->parent, $results, $displaycat);
            }
        }
    }

    /**
     * Get categories Hirearchy
     *
     * @param integer $parent         Category parent Id
     * @param string  $orderBy        Order
     * @param string  $orderDirection Order direction
     * @param array   $config         Config array
     *
     * @return array
     */
    public function getCategoriesHierarchy($parent = 0, $orderBy = 'term_group', $orderDirection = 'desc', $config = null)
    {
        $taxonomy   = 'wpfd-category';

        if ($orderBy === 'ordering' || $orderBy === '') {
            $orderBy = 'term_group';
        }

        if ($orderBy === 'title') {
            $orderBy = 'name';
        }

        if ($orderDirection === '') {
            $orderDirection = 'desc';
        }

        $categories = get_terms(array(
            'taxonomy'     => $taxonomy,
            'orderby'      => $orderBy,
            'order'        => $orderDirection,
            'hierarchical' => 0,
            'hide_empty'   => 0,
            'parent'       => $parent
        ));
        $user = wp_get_current_user();
        $roles = array();
        foreach ($user->roles as $role) {
            $roles[] = strtolower($role);
        }
        $result = array();
        if ($categories) {
            foreach ($categories as $category) {
                $category->name = html_entity_decode($category->name);
                $term_meta = get_option('taxonomy_' . $category->term_id);
                $params = json_decode($category->description, true);
                $defaultParams = array(
                    'order' => 'asc',
                    'orderby' => 'name',
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

                $categoryVisibility = wpfd_get_category_visibility($category->term_id);
                $cat_roles       = isset($categoryVisibility['roles']) ? $categoryVisibility['roles'] : array();
                $cat_access      = isset($categoryVisibility['access']) ? $categoryVisibility['access'] : 0;

                $category->ordering = isset($params['ordering']) ? (string) $params['ordering'] : $defaultParams['orderby'];
                $category->orderingdir = isset($params['orderingdir']) ? (string) $params['orderingdir'] : $defaultParams['order'];

                $allows_single = false;

                if (isset($params['canview']) && $params['canview'] !== '') {
                    if (((int) $params['canview'] !== 0) && (int) $params['canview'] === (int) $user->ID) {
                        $allows_single = true;
                    }
                }

                if ((int) $cat_access === 1) {
                    $allows = array_intersect($roles, $cat_roles);
                    if ($allows || $allows_single) {
                        $result[] = $category;
                    }
                } else {
                    $result[] = $category;
                }
            }
        }

        $terms = stripslashes_deep($result);
        $results = array();

        if (!empty($terms)) {
            foreach ($terms as $term) {
                $emptyChild = false;
                $children = get_term_children($term->term_id, 'wpfd-category');
                if (is_wp_error($children) || (is_array($children) && empty($children))) {
                    $emptyChild = true;
                }
                // Check multiple categories file
                $description = json_decode($term->description, true);
                if (!empty($description) && isset($description['refToFile']) && is_array($description['refToFile']) && !empty($description['refToFile'])) {
                    $emptyChild = false;
                }

                $term->children = $this->getCategoriesHierarchy($term->term_id, $orderBy, $orderDirection, $config);
                $the_query = new WP_Query(array(
                    'post_type' => 'wpfd_file',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'wpfd-category',
                            'field' => 'id',
                            'terms' => $term->term_id
                        )
                    )
                ));

                $fileInCategory = $the_query->found_posts;
                if ($emptyChild && empty($term->children) && intval($fileInCategory) === 0 && !is_null($config) && isset($config['show_empty_folder']) && intval($config['show_empty_folder']) === 0) {
                    // Check is cloud category?
                    $categoryFrom = apply_filters('wpfdAddonCategoryFrom', $term->term_id) ;
                    if (!in_array($categoryFrom, wpfd_get_support_cloud())) {
                        continue;
                    }
                }

                $results[] = $term;
            }
        }
        if ($orderBy === 'term_group') {
            usort($results, function ($a, $b) {
                return ($a->term_group < $b->term_group) ? -1 : 1;
            });
        }

        return $results;
    }
}
